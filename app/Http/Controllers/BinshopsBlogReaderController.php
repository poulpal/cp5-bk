<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Swis\Laravel\Fulltext\Search;
use BinshopsBlog\Captcha\UsesCaptcha;
use BinshopsBlog\Models\BinshopsBlogCategory;
use BinshopsBlog\Models\BinshopsBlogPost;

use Artesaos\SEOTools\Facades\SEOMeta;
use Artesaos\SEOTools\Facades\OpenGraph;
use Artesaos\SEOTools\Facades\TwitterCard;
use Artesaos\SEOTools\Facades\JsonLd;

/**
 * Class BinshopsBlogReaderController
 * All of the main public facing methods for viewing blog content (index, single posts)
 * @package BinshopsBlog\Controllers
 */
class BinshopsBlogReaderController extends Controller
{
    use UsesCaptcha;

    /**
     * Show blog posts
     * If category_slug is set, then only show from that category
     *
     * @param null $category_slug
     * @return mixed
     */
    public function index($category_slug = null)
    {

        SEOMeta::setTitle('بلاگ ' . config('app.name'));
        SEOMeta::setDescription('شارژپل، نرم افزار مدیریت ساختمان،  به شما این امکان را میدهد تا  امور مختلف ساختمان مانند شارژ ساختمان، حسابداری ساختمان، مالیات و اطلاعیه های امور ساختمان را  به راحتی مدیریت  کنید.');
        SEOMeta::addKeyword([
            'بیمه و مالیات شارژپل, شارژ, ساختمان, مدیریت, هوشمند, شارژ ساختمان, شارژپل'
        ]);

        OpenGraph::setDescription('شارژپل، نرم افزار مدیریت ساختمان،  به شما این امکان را میدهد تا  امور مختلف ساختمان مانند شارژ ساختمان، حسابداری ساختمان، مالیات و اطلاعیه های امور ساختمان را  به راحتی مدیریت  کنید.');
        OpenGraph::setTitle('بلاگ ' . config('app.name'));
        OpenGraph::addProperty('site_name', config('app.name'));
        OpenGraph::addProperty('locale', 'fa_IR');
        OpenGraph::addProperty('locale:alternate', 'En_US');

        // the published_at + is_published are handled by BinshopsBlogPublishedScope, and don't take effect if the logged in user can manageb log posts
        $title = 'Blog Page'; // default title...

        $categoryChain = null;
        if ($category_slug) {
            $category = BinshopsBlogCategory::where("slug", $category_slug)->firstOrFail();
            $categoryChain = $category->getAncestorsAndSelf();
            $posts = $category->posts()->where("binshops_blog_post_categories.binshops_blog_category_id", $category->id);

            // at the moment we handle this special case (viewing a category) by hard coding in the following two lines.
            // You can easily override this in the view files.
            \View::share('BinshopsBlog_category', $category); // so the view can say "You are viewing $CATEGORYNAME category posts"
            $title = 'Posts in ' . $category->category_name . " category"; // hardcode title here...
        } else {
            $posts = BinshopsBlogPost::query();
        }

        $posts = $posts->where('is_published', '=', 1)->where('posted_at', '<', Carbon::now()->format('Y-m-d H:i:s'))->orderBy("posted_at", "desc")->paginate(config("binshopsblog.per_page", 10));

        //load categories in 3 levels
        $rootList = BinshopsBlogCategory::where('parent_id', '=', null)->get();
        for ($i = 0; sizeof($rootList) > $i; $i++) {
            $rootList[$i]->loadSiblings();
            for ($j = 0; sizeof($rootList[$i]->siblings) > $j; $j++) {
                $rootList[$i]->siblings[$j]->loadSiblings();
            }
        }

        return view("binshopsblog::index", [
            'category_chain' => $categoryChain,
            'categories' => $rootList,
            'posts' => $posts,
            'title' => $title,
        ]);
    }

    /**
     * Show the search results for $_GET['s']
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * @throws \Exception
     */
    public function search(Request $request, $query)
    {
        if (!config("binshopsblog.search.search_enabled")) {
            throw new \Exception("Search is disabled");
        }
        // $query = $request->get("s");
        // replace + and - with space
        $query = str_replace(['+', '-'], ' ', $query);
        $search = new Search();
        $search_results = $search->run($query);

        \View::share("title", "Search results for " . e($query));

        $categories = BinshopsBlogCategory::all();

        return view(
            "binshopsblog::search",
            [
                'categories' => $categories,
                'query' => $query,
                'search_results' => $search_results
            ]
        );
    }

    /**
     * View all posts in $category_slug category
     *
     * @param Request $request
     * @param $category_slug
     * @return mixed
     */
    public function view_category($hierarchy)
    {
        $categories = explode('/', $hierarchy);
        return $this->index(end($categories));
    }

    /**
     * View a single post and (if enabled) it's comments
     *
     * @param Request $request
     * @param $blogPostSlug
     * @return mixed
     */
    public function viewSinglePost(Request $request, $blogPostSlug)
    {
        // the published_at + is_published are handled by BinshopsBlogPublishedScope, and don't take effect if the logged in user can manage log posts
        $blog_post = BinshopsBlogPost::where("slug", $blogPostSlug)
            ->firstOrFail();

        SEOMeta::setTitle($blog_post->title . ' - ' . config('app.name'));
        SEOMeta::setDescription($blog_post->short_description);
        SEOMeta::addMeta('article:published_time', $blog_post->posted_at->toW3CString(), 'property');
        SEOMeta::addMeta('article:section', $blog_post->categories->first()->category_name ?? 'Uncategorized', 'property');
        SEOMeta::addKeyword(array_merge(
            $blog_post->categories->pluck('category_name')->toArray(),
            [$blog_post->title]
        ));

        OpenGraph::setDescription($blog_post->short_description);
        OpenGraph::setTitle($blog_post->title . ' - ' . config('app.name'));
        OpenGraph::addProperty('type', 'article');
        OpenGraph::addProperty('site_name', config('app.name'));
        OpenGraph::addProperty('locale', 'fa_IR');
        OpenGraph::addProperty('locale:alternate', 'En_US');

        OpenGraph::addImage(asset('blog_images/' . $blog_post->image_medium));

        JsonLd::setTitle($blog_post->title);
        JsonLd::setDescription($blog_post->short_description);
        JsonLd::setType('BlogPosting');
        JsonLd::setImages([asset('blog_images/' . $blog_post->image_medium)]);

        JsonLd::addValue('mainEntityOfPage', [
            '@type' => 'WebPage',
            '@id' => $request->url(),
        ]);

        JsonLd::addValue('headline', $blog_post->title);
        JsonLd::addValue('image', asset('blog_images/' . $blog_post->image_medium));
        JsonLd::addValue('author', [
            '@type' => 'Organization',
            'name' => 'chargepal',
            'url' => 'https://chargepal.ir',
        ]);
        JsonLd::addValue('publisher', [
            '@type' => 'Organization',
            'name' => 'chargepal',
            'url' => 'https://chargepal.ir',
            'logo' => [
                '@type' => 'ImageObject',
                'url' => 'https://chargepal.ir/img/logo2.png',
            ],
        ]);
        JsonLd::addValue('datePublished', $blog_post->posted_at->toW3CString());
        JsonLd::addValue('dateModified', $blog_post->updated_at->toW3CString());

        if ($captcha = $this->getCaptchaObject()) {
            $captcha->runCaptchaBeforeShowingPosts($request, $blog_post);
        }

        return view("binshopsblog::single_post", [
            'post' => $blog_post,
            // the default scope only selects approved comments, ordered by id
            'comments' => $blog_post->comments()
                ->with("user")
                ->get(),
            'captcha' => $captcha,
        ]);
    }
}
