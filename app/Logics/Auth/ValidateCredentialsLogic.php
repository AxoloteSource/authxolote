<?php

namespace App\Logics\Auth;

use App\Data\Auth\ValidateCredentialsData;
use AxoloteSource\Logics\Logics\Logic;
use AxoloteSource\Logics\Traits\OnlyWithAction;
use Illuminate\Http\JsonResponse;
use Spatie\LaravelData\Data;

class ValidateCredentialsLogic extends Logic
{
    use OnlyWithAction;

    protected Data|ValidateCredentialsData $input;

    public function run(ValidateCredentialsData $input): JsonResponse
    {
        return parent::logic($input);
    }

    protected function action(): Logic
    {
        $this->setResponse([
            'valid' => auth()->validate([
                'email' => $this->input->email,
                'password' => $this->input->password,
            ]),
        ]);

        return $this;
    }
}
