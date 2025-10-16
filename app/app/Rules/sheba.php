<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class Sheba implements Rule
{
    private $length = 26;

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value){
        return (boolean) ((strlen($value) === ($this->length - 2)) && $this->getChecksum('IR' . $value) === 1 );
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'شماره  شبا استاندارد نیست.';
    }

    private function getChecksum($iban)
    {
        $iban = substr($iban, 4) . substr($iban, 0, 4);
        $iban = str_replace(
            $this->getReplacementsChars(),
            $this->getReplacementsValues(),
            $iban
        );

        $checksum = intval(substr($iban, 0, 1));

        for ($strcounter = 1; $strcounter < strlen($iban); $strcounter++) {
            $checksum *= 10;
            $checksum += intval(substr($iban, $strcounter, 1));
            $checksum %= 97;
        }

        return $checksum; // only 1 is iban
    }

    private function getReplacementsChars()
    {
        return range('A', 'Z');
    }

    private function getReplacementsValues()
    {
        $values = [];
        foreach (range(10, 35) as $value) {
            $values[] = strval($value);
        }

        return $values;
    }
}
