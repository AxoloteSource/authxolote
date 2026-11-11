<?php

namespace App\Logics\PersonalAccessToken;

use App\Data\PersonalAccessToken\PersonalAccessTokenIndexData;
use App\Http\Resources\PersonalAccessToken\PersonalAccessTokenIndexResource;
use AxoloteSource\Logics\Logics\IndexLogic;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Laravel\Passport\Token;
use Spatie\LaravelData\Data;

class PersonalAccessTokenIndexLogic extends IndexLogic
{
    public Model|Token $model;

    public PersonalAccessTokenIndexData|Data $input;

    protected array $allowOrderByFields = [
        'name',
        'expires_at',
        'revoked',
        'created_at',
    ];

    public function __construct(Token $model)
    {
        parent::__construct($model);
    }

    public function run(PersonalAccessTokenIndexData|Data $input): JsonResponse
    {
        return parent::logic($input);
    }

    public function makeQuery(): Builder
    {
        return $this->model->newQuery()
            ->with('user')
            ->whereHas('client', fn (Builder $query) => $query->where('personal_access_client', true))
            ->when($this->input->user_id, fn (Builder $query, string $userId) => $query->where('user_id', $userId))
            ->latest('created_at');
    }

    protected function getColumnSearch(): string
    {
        return 'name';
    }

    protected function tableHeaders(): array
    {
        return [
            'name' => __('Nombre'),
            'user' => __('Usuario'),
            'expires_at' => __('Expira'),
            'revoked' => __('Revocado'),
            'created_at' => __('Fecha de creación'),
        ];
    }

    protected function withResource(): mixed
    {
        return PersonalAccessTokenIndexResource::collection($this->response);
    }
}
