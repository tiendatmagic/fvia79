<?php

namespace App\Infrastructure\Persistence\Mappers;

use App\Domain\Entities\Deposit;
use App\Infrastructure\Persistence\Eloquent\DepositModel;

class DepositMapper
{
    public static function toDomain(DepositModel $model): Deposit
    {
        return new Deposit(
            $model->id,
            $model->user_id,
            $model->code,
            (float) $model->amount_usdt,
            (float) $model->amount_to_pay,
            (float) $model->amount_vnd,
            $model->tx_hash,
            $model->admin_wallet_address,
            $model->status,
            $model->created_at ? new \DateTime($model->created_at->toDateTimeString()) : null,
            $model->updated_at ? new \DateTime($model->updated_at->toDateTimeString()) : null
        );
    }

    public static function toPersistence(Deposit $entity): DepositModel
    {
        $model = new DepositModel();
        if ($entity->getId()) {
            $model = DepositModel::find($entity->getId()) ?: $model;
        }

        $model->user_id = $entity->getUserId();
        $model->code = $entity->getCode();
        $model->amount_usdt = $entity->getAmountUsdt();
        $model->amount_to_pay = $entity->getAmountToPay();
        $model->amount_vnd = $entity->getAmountVnd();
        $model->tx_hash = $entity->getTxHash();
        $model->admin_wallet_address = $entity->getAdminWalletAddress();
        $model->status = $entity->getStatus();

        return $model;
    }
}
