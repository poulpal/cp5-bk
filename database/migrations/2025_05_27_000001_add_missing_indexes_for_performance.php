<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        $sm = Schema::getConnection()->getDoctrineSchemaManager();
        // RESERVATIONS
        $indexes = $sm->listTableIndexes('reservations');
        if (!isset($indexes['reservations_reservable_id_index'])) {
            Schema::table('reservations', function (Blueprint $table) {
                $table->index('reservable_id');
            });
        }
        if (!isset($indexes['reservations_user_id_index'])) {
            Schema::table('reservations', function (Blueprint $table) {
                $table->index('user_id');
            });
        }
        if (!isset($indexes['reservations_unit_id_index'])) {
            Schema::table('reservations', function (Blueprint $table) {
                $table->index('unit_id');
            });
        }
        $foreigns = $sm->listTableForeignKeys('reservations');
        $foreignNames = array_map(function ($fk) {
            return $fk->getName();
        }, $foreigns);
        if (!in_array('reservations_reservable_id_foreign', $foreignNames)) {
            Schema::table('reservations', function (Blueprint $table) {
                $table->foreign('reservable_id')->references('id')->on('reservables')->onDelete('no action');
            });
        }
        if (!in_array('reservations_user_id_foreign', $foreignNames)) {
            Schema::table('reservations', function (Blueprint $table) {
                $table->foreign('user_id')->references('id')->on('users')->onDelete('no action');
            });
        }
        if (!in_array('reservations_unit_id_foreign', $foreignNames)) {
            Schema::table('reservations', function (Blueprint $table) {
                $table->foreign('unit_id')->references('id')->on('building_units')->onDelete('no action');
            });
        }
        // PENDING_DEPOSITS
        $indexes = $sm->listTableIndexes('pending_deposits');
        if (!isset($indexes['pending_deposits_invoice_id_index'])) {
            Schema::table('pending_deposits', function (Blueprint $table) {
                $table->index('invoice_id');
            });
        }
        if (!isset($indexes['pending_deposits_building_id_index'])) {
            Schema::table('pending_deposits', function (Blueprint $table) {
                $table->index('building_id');
            });
        }
        $foreigns = $sm->listTableForeignKeys('pending_deposits');
        $foreignNames = array_map(function ($fk) {
            return $fk->getName();
        }, $foreigns);
        if (!in_array('pending_deposits_invoice_id_foreign', $foreignNames)) {
            Schema::table('pending_deposits', function (Blueprint $table) {
                $table->foreign('invoice_id')->references('id')->on('invoices')->onDelete('no action');
            });
        }
        if (!in_array('pending_deposits_building_id_foreign', $foreignNames)) {
            Schema::table('pending_deposits', function (Blueprint $table) {
                $table->foreign('building_id')->references('id')->on('buildings')->onDelete('no action');
            });
        }
        // POLL_VOTES
        $indexes = $sm->listTableIndexes('poll_votes');
        if (!isset($indexes['poll_votes_poll_id_index'])) {
            Schema::table('poll_votes', function (Blueprint $table) {
                $table->index('poll_id');
            });
        }
        if (!isset($indexes['poll_votes_building_unit_id_index'])) {
            Schema::table('poll_votes', function (Blueprint $table) {
                $table->index('building_unit_id');
            });
        }
        if (!isset($indexes['poll_votes_user_id_index'])) {
            Schema::table('poll_votes', function (Blueprint $table) {
                $table->index('user_id');
            });
        }
        $foreigns = $sm->listTableForeignKeys('poll_votes');
        $foreignNames = array_map(function ($fk) {
            return $fk->getName();
        }, $foreigns);
        if (!in_array('poll_votes_poll_id_foreign', $foreignNames)) {
            Schema::table('poll_votes', function (Blueprint $table) {
                $table->foreign('poll_id')->references('id')->on('polls')->onDelete('no action');
            });
        }
        if (!in_array('poll_votes_building_unit_id_foreign', $foreignNames)) {
            Schema::table('poll_votes', function (Blueprint $table) {
                $table->foreign('building_unit_id')->references('id')->on('building_units')->onDelete('no action');
            });
        }
        if (!in_array('poll_votes_user_id_foreign', $foreignNames)) {
            Schema::table('poll_votes', function (Blueprint $table) {
                $table->foreign('user_id')->references('id')->on('users')->onDelete('no action');
            });
        }
        // REFERALLS
        $indexes = $sm->listTableIndexes('referalls');
        if (!isset($indexes['referalls_user_id_index'])) {
            Schema::table('referalls', function (Blueprint $table) {
                $table->index('user_id');
            });
        }
        if (!isset($indexes['referalls_building_id_index'])) {
            Schema::table('referalls', function (Blueprint $table) {
                $table->index('building_id');
            });
        }
        $foreigns = $sm->listTableForeignKeys('referalls');
        $foreignNames = array_map(function ($fk) {
            return $fk->getName();
        }, $foreigns);
        if (!in_array('referalls_user_id_foreign', $foreignNames)) {
            Schema::table('referalls', function (Blueprint $table) {
                $table->foreign('user_id')->references('id')->on('users')->onDelete('no action');
            });
        }
        if (!in_array('referalls_building_id_foreign', $foreignNames)) {
            Schema::table('referalls', function (Blueprint $table) {
                $table->foreign('building_id')->references('id')->on('buildings')->onDelete('no action');
            });
        }
        // ATTACHMENTS
        $indexes = $sm->listTableIndexes('attachments');
        if (!isset($indexes['attachments_attachable_id_attachable_type_index'])) {
            Schema::table('attachments', function (Blueprint $table) {
                $table->index(['attachable_id', 'attachable_type']);
            });
        }
        // BUILDING_UNITS_USERS
        $foreigns = $sm->listTableForeignKeys('building_units_users');
        $foreignNames = array_map(function ($fk) {
            return $fk->getName();
        }, $foreigns);
        if (!in_array('building_units_users_building_unit_id_foreign', $foreignNames)) {
            Schema::table('building_units_users', function (Blueprint $table) {
                $table->foreign('building_unit_id')->references('id')->on('building_units')->onDelete('no action');
            });
        }
        if (!in_array('building_units_users_user_id_foreign', $foreignNames)) {
            Schema::table('building_units_users', function (Blueprint $table) {
                $table->foreign('user_id')->references('id')->on('users')->onDelete('no action');
            });
        }
        // ACCOUNTING_ACCOUNTS
        $indexes = $sm->listTableIndexes('accounting_accounts');
        if (!isset($indexes['accounting_accounts_building_id_index'])) {
            Schema::table('accounting_accounts', function (Blueprint $table) {
                $table->index('building_id');
            });
        }
        if (!isset($indexes['accounting_accounts_parent_id_index'])) {
            Schema::table('accounting_accounts', function (Blueprint $table) {
                $table->index('parent_id');
            });
        }
        $foreigns = $sm->listTableForeignKeys('accounting_accounts');
        $foreignNames = array_map(function ($fk) {
            return $fk->getName();
        }, $foreigns);
        if (!in_array('accounting_accounts_building_id_foreign', $foreignNames)) {
            Schema::table('accounting_accounts', function (Blueprint $table) {
                $table->foreign('building_id')->references('id')->on('buildings')->onDelete('no action');
            });
        }
        if (!in_array('accounting_accounts_parent_id_foreign', $foreignNames)) {
            Schema::table('accounting_accounts', function (Blueprint $table) {
                $table->foreign('parent_id')->references('id')->on('accounting_accounts')->onDelete('no action');
            });
        }
        // ACCOUNTING_DETAILS
        $indexes = $sm->listTableIndexes('accounting_details');
        if (!isset($indexes['accounting_details_building_id_index'])) {
            Schema::table('accounting_details', function (Blueprint $table) {
                $table->index('building_id');
            });
        }
        if (!isset($indexes['accounting_details_parent_id_index'])) {
            Schema::table('accounting_details', function (Blueprint $table) {
                $table->index('parent_id');
            });
        }
        $foreigns = $sm->listTableForeignKeys('accounting_details');
        $foreignNames = array_map(function ($fk) {
            return $fk->getName();
        }, $foreigns);
        if (!in_array('accounting_details_building_id_foreign', $foreignNames)) {
            Schema::table('accounting_details', function (Blueprint $table) {
                $table->foreign('building_id')->references('id')->on('buildings')->onDelete('no action');
            });
        }
        if (!in_array('accounting_details_parent_id_foreign', $foreignNames)) {
            Schema::table('accounting_details', function (Blueprint $table) {
                $table->foreign('parent_id')->references('id')->on('accounting_details')->onDelete('no action');
            });
        }
        // ACCOUNTING_DOCUMENTS
        $indexes = $sm->listTableIndexes('accounting_documents');
        if (!isset($indexes['accounting_documents_building_id_index'])) {
            Schema::table('accounting_documents', function (Blueprint $table) {
                $table->index('building_id');
            });
        }
        $foreigns = $sm->listTableForeignKeys('accounting_documents');
        $foreignNames = array_map(function ($fk) {
            return $fk->getName();
        }, $foreigns);
        if (!in_array('accounting_documents_building_id_foreign', $foreignNames)) {
            Schema::table('accounting_documents', function (Blueprint $table) {
                $table->foreign('building_id')->references('id')->on('buildings')->onDelete('no action');
            });
        }
        // ACCOUNTING_TRANSACTIONS
        $indexes = $sm->listTableIndexes('accounting_transactions');
        if (!isset($indexes['accounting_transactions_accounting_document_id_index'])) {
            Schema::table('accounting_transactions', function (Blueprint $table) {
                $table->index('accounting_document_id');
            });
        }
        if (!isset($indexes['accounting_transactions_accounting_account_id_index'])) {
            Schema::table('accounting_transactions', function (Blueprint $table) {
                $table->index('accounting_account_id');
            });
        }
        if (!isset($indexes['accounting_transactions_accounting_detail_id_index'])) {
            Schema::table('accounting_transactions', function (Blueprint $table) {
                $table->index('accounting_detail_id');
            });
        }
        $foreigns = $sm->listTableForeignKeys('accounting_transactions');
        $foreignNames = array_map(function ($fk) {
            return $fk->getName();
        }, $foreigns);
        if (!in_array('accounting_transactions_accounting_document_id_foreign', $foreignNames)) {
            Schema::table('accounting_transactions', function (Blueprint $table) {
                $table->foreign('accounting_document_id')->references('id')->on('accounting_documents')->onDelete('no action');
            });
        }
        if (!in_array('accounting_transactions_accounting_account_id_foreign', $foreignNames)) {
            Schema::table('accounting_transactions', function (Blueprint $table) {
                $table->foreign('accounting_account_id')->references('id')->on('accounting_accounts')->onDelete('no action');
            });
        }
        if (!in_array('accounting_transactions_accounting_detail_id_foreign', $foreignNames)) {
            Schema::table('accounting_transactions', function (Blueprint $table) {
                $table->foreign('accounting_detail_id')->references('id')->on('accounting_details')->onDelete('no action');
            });
        }
        // DEBT_TYPES
        $indexes = $sm->listTableIndexes('debt_types');
        if (!isset($indexes['debt_types_building_id_index'])) {
            Schema::table('debt_types', function (Blueprint $table) {
                $table->index('building_id');
            });
        }
        if (!isset($indexes['debt_types_receivable_accounting_account_id_index'])) {
            Schema::table('debt_types', function (Blueprint $table) {
                $table->index('receivable_accounting_account_id');
            });
        }
        if (!isset($indexes['debt_types_income_accounting_account_id_index'])) {
            Schema::table('debt_types', function (Blueprint $table) {
                $table->index('income_accounting_account_id');
            });
        }
        $foreigns = $sm->listTableForeignKeys('debt_types');
        $foreignNames = array_map(function ($fk) {
            return $fk->getName();
        }, $foreigns);
        if (!in_array('debt_types_building_id_foreign', $foreignNames)) {
            Schema::table('debt_types', function (Blueprint $table) {
                $table->foreign('building_id')->references('id')->on('buildings')->onDelete('no action');
            });
        }
        if (!in_array('debt_types_receivable_accounting_account_id_foreign', $foreignNames)) {
            Schema::table('debt_types', function (Blueprint $table) {
                $table->foreign('receivable_accounting_account_id')->references('id')->on('accounting_accounts')->onDelete('no action');
            });
        }
        if (!in_array('debt_types_income_accounting_account_id_foreign', $foreignNames)) {
            Schema::table('debt_types', function (Blueprint $table) {
                $table->foreign('income_accounting_account_id')->references('id')->on('accounting_accounts')->onDelete('no action');
            });
        }
        // BUILDING_UNITS
        $indexes = $sm->listTableIndexes('building_units');
        if (!isset($indexes['building_units_building_id_index'])) {
            Schema::table('building_units', function (Blueprint $table) {
                $table->index('building_id');
            });
        }
        if (!isset($indexes['building_units_token_index'])) {
            Schema::table('building_units', function (Blueprint $table) {
                $table->index('token');
            });
        }
        $foreigns = $sm->listTableForeignKeys('building_units');
        $foreignNames = array_map(function ($fk) {
            return $fk->getName();
        }, $foreigns);
        if (!in_array('building_units_building_id_foreign', $foreignNames)) {
            Schema::table('building_units', function (Blueprint $table) {
                $table->foreign('building_id')->references('id')->on('buildings')->onDelete('no action');
            });
        }
        // MODULES
        $indexes = $sm->listTableIndexes('modules');
        if (!isset($indexes['modules_slug_index'])) {
            Schema::table('modules', function (Blueprint $table) {
                $table->index('slug');
            });
        }
        // BUILDINGS_MODULES
        $indexes = $sm->listTableIndexes('buildings_modules');
        if (!isset($indexes['buildings_modules_building_id_index'])) {
            Schema::table('buildings_modules', function (Blueprint $table) {
                $table->index('building_id');
            });
        }
        if (!isset($indexes['buildings_modules_module_slug_index'])) {
            Schema::table('buildings_modules', function (Blueprint $table) {
                $table->index('module_slug');
            });
        }
        $foreigns = $sm->listTableForeignKeys('buildings_modules');
        $foreignNames = array_map(function ($fk) {
            return $fk->getName();
        }, $foreigns);
        if (!in_array('buildings_modules_building_id_foreign', $foreignNames)) {
            Schema::table('buildings_modules', function (Blueprint $table) {
                $table->foreign('building_id')->references('id')->on('buildings')->onDelete('no action');
            });
        }
        // DISCOUNT_CODES
        $indexes = $sm->listTableIndexes('discount_codes');
        if (!isset($indexes['discount_codes_code_index'])) {
            Schema::table('discount_codes', function (Blueprint $table) {
                $table->index('code');
            });
        }
        // SURVEYS
        $indexes = $sm->listTableIndexes('surveys');
        if (!isset($indexes['surveys_slug_index'])) {
            Schema::table('surveys', function (Blueprint $table) {
                $table->index('slug');
            });
        }
        // PLANS
        $indexes = $sm->listTableIndexes('plans');
        if (!isset($indexes['plans_slug_index'])) {
            Schema::table('plans', function (Blueprint $table) {
                $table->index('slug');
            });
        }
        // FORUM_LIKES
        $indexes = $sm->listTableIndexes('forum_likes');
        if (!isset($indexes['forum_likes_forum_post_id_index'])) {
            Schema::table('forum_likes', function (Blueprint $table) {
                $table->index('forum_post_id');
            });
        }
        if (!isset($indexes['forum_likes_user_id_index'])) {
            Schema::table('forum_likes', function (Blueprint $table) {
                $table->index('user_id');
            });
        }
        $foreigns = $sm->listTableForeignKeys('forum_likes');
        $foreignNames = array_map(function ($fk) {
            return $fk->getName();
        }, $foreigns);
        if (!in_array('forum_likes_forum_post_id_foreign', $foreignNames)) {
            Schema::table('forum_likes', function (Blueprint $table) {
                $table->foreign('forum_post_id')->references('id')->on('forum_posts')->onDelete('no action');
            });
        }
        if (!in_array('forum_likes_user_id_foreign', $foreignNames)) {
            Schema::table('forum_likes', function (Blueprint $table) {
                $table->foreign('user_id')->references('id')->on('users')->onDelete('no action');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropForeign(['reservable_id']);
            $table->dropForeign(['user_id']);
            $table->dropForeign(['unit_id']);
            $table->dropIndex(['reservable_id']);
            $table->dropIndex(['user_id']);
            $table->dropIndex(['unit_id']);
        });
        Schema::table('pending_deposits', function (Blueprint $table) {
            $table->dropForeign(['invoice_id']);
            $table->dropForeign(['building_id']);
            $table->dropIndex(['invoice_id']);
            $table->dropIndex(['building_id']);
        });
        Schema::table('poll_votes', function (Blueprint $table) {
            $table->dropForeign(['poll_id']);
            $table->dropForeign(['building_unit_id']);
            $table->dropForeign(['user_id']);
            $table->dropIndex(['poll_id']);
            $table->dropIndex(['building_unit_id']);
            $table->dropIndex(['user_id']);
        });
        Schema::table('referalls', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['building_id']);
            $table->dropIndex(['user_id']);
            $table->dropIndex(['building_id']);
        });
        Schema::table('attachments', function (Blueprint $table) {
            $table->dropIndex(['attachable_id', 'attachable_type']);
        });
        Schema::table('building_units_users', function (Blueprint $table) {
            $table->dropForeign(['building_unit_id']);
            $table->dropForeign(['user_id']);
        });
        Schema::table('accounting_accounts', function (Blueprint $table) {
            $table->dropForeign(['building_id']);
            $table->dropForeign(['parent_id']);
            $table->dropIndex(['building_id']);
            $table->dropIndex(['parent_id']);
        });
        Schema::table('accounting_details', function (Blueprint $table) {
            $table->dropForeign(['building_id']);
            $table->dropForeign(['parent_id']);
            $table->dropIndex(['building_id']);
            $table->dropIndex(['parent_id']);
        });
        Schema::table('accounting_documents', function (Blueprint $table) {
            $table->dropForeign(['building_id']);
            $table->dropIndex(['building_id']);
        });
        Schema::table('accounting_transactions', function (Blueprint $table) {
            $table->dropForeign(['accounting_document_id']);
            $table->dropForeign(['account_id']);
            $table->dropForeign(['accounting_detail_id']);
            $table->dropIndex(['accounting_document_id']);
            $table->dropIndex(['account_id']);
            $table->dropIndex(['accounting_detail_id']);
        });
        Schema::table('debt_types', function (Blueprint $table) {
            $table->dropForeign(['building_id']);
            $table->dropForeign(['receivable_accounting_account_id']);
            $table->dropForeign(['income_accounting_account_id']);
            $table->dropIndex(['building_id']);
            $table->dropIndex(['receivable_accounting_account_id']);
            $table->dropIndex(['income_accounting_account_id']);
        });
        Schema::table('building_units', function (Blueprint $table) {
            $table->dropForeign(['building_id']);
            $table->dropIndex(['building_id']);
            $table->dropIndex(['token']);
        });
        Schema::table('modules', function (Blueprint $table) {
            $table->dropIndex(['slug']);
        });
        Schema::table('buildings_modules', function (Blueprint $table) {
            $table->dropForeign(['building_id']);
            $table->dropIndex(['building_id']);
            $table->dropIndex(['module_slug']);
        });
        Schema::table('discount_codes', function (Blueprint $table) {
            $table->dropIndex(['code']);
        });
        Schema::table('surveys', function (Blueprint $table) {
            $table->dropIndex(['slug']);
        });
        Schema::table('plans', function (Blueprint $table) {
            $table->dropIndex(['slug']);
        });
        Schema::table('forum_likes', function (Blueprint $table) {
            $table->dropForeign(['forum_post_id']);
            $table->dropForeign(['user_id']);
            $table->dropIndex(['forum_post_id']);
            $table->dropIndex(['user_id']);
        });
    }
};
