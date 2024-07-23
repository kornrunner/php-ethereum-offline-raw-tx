<?php

namespace kornrunner;

use kornrunner\Ethereum\Transaction;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class TransactionTest extends TestCase
{

    /**
     * @dataProvider input
     */
    public function testGetInput($expect, $nonce, $gasPrice, $gasLimit, $to, $value, $data)
    {
        $transaction = new Transaction($nonce, $gasPrice, $gasLimit, $to, $value, $data);
        $this->assertSame($expect, $transaction->getInput());
    }

    public static function input(): array
    {
        return [
            [
                ['nonce' => '', 'gasPrice' => '', 'gasLimit' => '', 'to' => '', 'value' => '', 'data' => '', 'v' => '', 'r' => '', 's' => ''],
                '', '', '', '', '', ''
            ],
            [
                ['nonce' => '04', 'gasPrice' => '03f5476a00', 'gasLimit' => '027f4b', 'to' => '1a8c8adfbe1c59e8b58cc0d515f07b7225f51c72', 'value' => '2a45907d1bef7c00', 'data' => '', 'v' => '', 'r' => '', 's' => ''],
                '04', '03f5476a00', '027f4b', '1a8c8adfbe1c59e8b58cc0d515f07b7225f51c72', '2a45907d1bef7c00', ''
            ],
        ];
    }

    /**
     * @dataProvider getTransactionData
     */
    public function testGetRaw($txid, $unsigned, $signed, $privateKey, $chainId, $nonce, $gasPrice, $gasLimit, $to, $value, $data)
    {
        $transaction = new Transaction($nonce, $gasPrice, $gasLimit, $to, $value, $data);
        $this->assertSame($signed, $transaction->getRaw($privateKey, $chainId));
    }

    /**
     * @dataProvider getTransactionData
     */
    public function getUnsigned($txid, $unsigned, $signed, $privateKey, $chainId, $nonce, $gasPrice, $gasLimit, $to, $value, $data)
    {
        $transaction = new Transaction($nonce, $gasPrice, $gasLimit, $to, $value, $data);
        $this->assertSame($unsigned, $transaction->getUnsigned($chainId));
    }

    /**
     * @dataProvider getTransactionData
     */
    public function testHash($txid, $unsigned, $signed, $privateKey, $chainId, $nonce, $gasPrice, $gasLimit, $to, $value, $data)
    {
        $transaction = new Transaction($nonce, $gasPrice, $gasLimit, $to, $value, $data);
        $this->assertSame($txid, $transaction->hash($chainId));
    }

    public static function getTransactionData(): array
    {
        return [
            [
                'a0c5887430a600aac139fc269a354b0e5d4c700c3e177ea2c227502367224e9c', //txid
                'ea048503f5476a0083027f4b941a8c8adfbe1c59e8b58cc0d515f07b7225f51c72882a45907d1bef7c0080', //unsigned
                'f86d048503f5476a0083027f4b941a8c8adfbe1c59e8b58cc0d515f07b7225f51c72882a45907d1bef7c00801ba0e68be766b40702e6d9c419f53d5e053c937eda36f0e973074d174027439e2b5da0790df3e4d0294f92d69104454cd96005e21095efd5f2970c2829736ca39195d8',
                'b2f2698dd7343fa5afc96626dee139cb92e58e5d04e855f4c712727bf198e898', 0, '04', '03f5476a00', '027f4b', '1a8c8adfbe1c59e8b58cc0d515f07b7225f51c72', '2a45907d1bef7c00', ''
            ],
            [
                'db734dc3a2da9bee8baf350f7d6e6f2dc593b06534a7721385cdb588431b6136', //txid
                'ed048503f5476a0083027f4b941a8c8adfbe1c59e8b58cc0d515f07b7225f51c72882a45907d1bef7c0080018080', //unsigned
                'f86d048503f5476a0083027f4b941a8c8adfbe1c59e8b58cc0d515f07b7225f51c72882a45907d1bef7c008025a0db4efcc22a7d9b2cab180ce37f81959412594798cb9af7c419abb6323763cdd5a0631a0c47d27e5b6e3906a419de2d732e290b73ead4172d8598ce4799c13bda69',
                'b2f2698dd7343fa5afc96626dee139cb92e58e5d04e855f4c712727bf198e898', 1, '04', '03f5476a00', '027f4b', '1a8c8adfbe1c59e8b58cc0d515f07b7225f51c72', '2a45907d1bef7c00', ''
            ],
            [
                '8e5a5404792108371948015b678cbe6a10d0cf40c9969eeb04829a6714170ff6', //txid
                'ed048503f5476a0083027f4b941a8c8adfbe1c59e8b58cc0d515f07b7225f51c72882a45907d1bef7c0080388080', //unsigned
                'f86e048503f5476a0083027f4b941a8c8adfbe1c59e8b58cc0d515f07b7225f51c72882a45907d1bef7c00808193a05c206269d6d902591b5b37ec821be99f78f1375191b8a8fb5a9e7bd87cb33999a027ca8262e92e3a5d7d2682d0394587bd7362250f4313767aa1a656c075d09911',
                'b2f2698dd7343fa5afc96626dee139cb92e58e5d04e855f4c712727bf198e898', 56, '04', '03f5476a00', '027f4b', '1a8c8adfbe1c59e8b58cc0d515f07b7225f51c72', '2a45907d1bef7c00', ''
            ],
        ];
    }

    public function testGetRawBadChainId()
    {
        $this->expectException(RuntimeException::class);
        $transaction = new Transaction('04', '03f5476a00', '027f4b', '1a8c8adfbe1c59e8b58cc0d515f07b7225f51c72', '2a45907d1bef7c00', '');
        $transaction->getRaw('b2f2698dd7343fa5afc96626dee139cb92e58e5d04e855f4c712727bf198e898', -1);
    }

    public function testBadPrivateKey()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Incorrect private key');
        $transaction = new Transaction();
        $transaction->getRaw('');
    }
}
