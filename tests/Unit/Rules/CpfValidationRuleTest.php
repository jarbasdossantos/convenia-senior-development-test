<?php

namespace Tests\Unit\Rules;

use App\Rules\CpfValidationRule;
use Tests\TestCase;

class CpfValidationRuleTest extends TestCase
{
    private CpfValidationRule $rule;

    protected function setUp(): void
    {
        parent::setUp();
        $this->rule = new CpfValidationRule();
    }

    public function test_valid_cpf_passes(): void
    {
        $validCpfs = [
            '474.948.290-08',
            '424.385.130-12',
            '490.802.890-78',
            '690.333.010-08',
            '69303334094',
            '87300978002',
        ];

        foreach ($validCpfs as $cpf) {
            $failed = false;

            $this->rule->validate('cpf', $cpf, function ($message) use (&$failed) {
                $failed = true;
            });

            $this->assertFalse($failed, "O campo :attribute não é um CPF válido.");
        }
    }

    public function test_invalid_cpf_fails(): void
    {
        $invalidCpfs = [
            '123.456.789-00', // Wrong verification digits
            '987.654.321-99', // Wrong verification digits
            '111.111.111-11',
            '000.000.000-00',
            '999.999.999-99',
            '123.456.789',
            '123.456.789-090', // Too long
            'abc.def.ghi-jk',
            '',
            'invalid-cpf',
        ];

        foreach ($invalidCpfs as $cpf) {
            $failed = false;
            $failMessage = '';
            $failCallback = function ($message) use (&$failed, &$failMessage) {
                $failed = true;
                $failMessage = $message;
            };

            $this->rule->validate('cpf', $cpf, $failCallback);

            $this->assertEquals('O campo :attribute não é um CPF válido.', $failMessage);
        }
    }

    public function test_cpf_with_different_formatting_passes(): void
    {
        $validCpfs = [
            '123.456.789-09', // Standard format
            '12345678909',    // No formatting
            '123 456 789 09',
            '123-456-789-09',
            '123.456.789.09',
        ];

        foreach ($validCpfs as $cpf) {
            $failed = false;
            $failCallback = function ($message) use (&$failed) {
                $failed = true;
            };

            $this->rule->validate('cpf', $cpf, $failCallback);

            $this->assertFalse($failed, "CPF {$cpf} with different formatting should be valid");
        }
    }

    public function test_null_cpf_fails(): void
    {
        $failed = false;
        $failMessage = '';
        $failCallback = function ($message) use (&$failed, &$failMessage) {
            $failed = true;
            $failMessage = $message;
        };

        $this->rule->validate('cpf', null, $failCallback);

        $this->assertTrue($failed);
        $this->assertEquals('O campo :attribute não é um CPF válido.', $failMessage);
    }
}
