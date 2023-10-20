<?php

namespace backend\modules\balance\models;

use common\models\User;

/**
 * This is the model class for table "balance_operation_item".
 *
 * @property int              $id
 * @property int              $operation_id
 * @property string           $description
 * @property int              $price
 * @property int              $discount
 * @property int              $year_discount
 * @property int              $full_price
 * @property int              $sum
 * @property int              $balance
 * @property int              $bonus
 * @property int|null         $created_by
 * @property string           $created_at
 *
 * @property BalanceOperation $operation
 * @property User             $createdBy
 */
class BalanceOperationItem extends \yii\db\ActiveRecord
{

    public const DAYS_IN_MONTH = 30.4375;

    public static function tableName()
    {
        return 'balance_operation_item';
    }

    public function rules()
    {
        return [
        ];
    }

    public function beforeSave($insert)
    {
        if ($insert) {
            $this->created_by = \Yii::$app->user->id ?? null;
        }
        return parent::beforeSave($insert);
    }

    public function getSum(int $months = 0, int $days = 0): int
    {
        $months           = $months + $days / self::DAYS_IN_MONTH;
        $this->full_price = $this->price - $this->discount - $this->year_discount;
        $this->sum        = $this->balance = round($this->full_price * $months);
        $this->bonus      = 0;
        return $this->sum;
    }

    /**
     * @param BalanceOperationItem[] $items
     */
    public static function getSumItems(array $items = [], int $months = 0, int $days = 0): int
    {
        $sum = 0;
        foreach ($items as $item) {
            $sum += $item->getSum($months, $days);
        }
        return $sum;
    }

    /**
     * @param BalanceOperationItem[] $items
     */
    public static function distributeBonus(array $items = [], int $bonus = 0): void
    {
        $bonus = abs($bonus);
        foreach ($items as $item) {
            if ($bonus === 0) {
                return;
            }
            if ($item->balance <= $bonus) {
                $item->bonus   = $item->balance;
                $item->balance = 0;
                $bonus         -= $item->bonus;
            } else {
                $item->balance -= $bonus;
                $item->bonus   = $bonus;
                $bonus         = 0;
            }
        }
    }

    /**
     * @param BalanceOperationItem[] $items
     */
    public static function saveAll(array $items, int $operationId): void
    {
        foreach ($items as $item) {
            $item->operation_id = $operationId;
            $item->save(false);
        }
    }

    public function getOperation()
    {
        return $this->hasOne(BalanceOperation::class, ['id' => 'operation_id']);
    }

    public function getCreatedBy()
    {
        return $this->hasOne(User::class, ['id' => 'created_by']);
    }
}
