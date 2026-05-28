<?php

namespace Rrq\Vexagame\Facades;

use Illuminate\Support\Facades\Facade;
use Rrq\Vexagame\VexaGame;

/**
 * @method static array getProducts(?string $categorySlug = null)
 * @method static array getProductItems(string $productSlug)
 * @method static array getProductCategories()
 * @method static array createTransaction(string $code, string $customerNo, string $paymentMethod = 'balance', int $qty = 1, ?string $partnerRefId = null, ?int $maxPrice = null)
 * @method static array getTransactions(?string $code = null, int $page = 1)
 * @method static array getTransaction(string|int $id)
 * @method static array getProfile()
 * @method static int getBalance()
 * @method static array checkNickname(string $customerNo, string $game)
 *
 * @see VexaGame
 */
class VexaGameFacade extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return VexaGame::class;
    }
}
