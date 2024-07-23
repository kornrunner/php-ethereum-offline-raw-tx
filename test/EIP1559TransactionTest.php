<?php

namespace kornrunner;

use kornrunner\Ethereum\EIP1559Transaction;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class EIP1559TransactionTest extends TestCase
{

    /**
     * @dataProvider input
     */
    public function testGetInput ($expect, $nonce, $gasPrice, $gasLimit, $to, $value, $data)
    {
        $transaction = new EIP1559Transaction($nonce, $gasPrice, $gasLimit, $to, $value, $data);
        $this->assertSame($expect, $transaction->getInput());
    }

    public static function input (): array {
        return [
            [
                ['chainId' => null, 'nonce' => '', 'maxPriorityFeePerGas' => '', 'maxFeePerGas' => '', 'gasLimit' => '', 'to' => '', 'value' => '', 'data' => '', 'accessList' => [], 'v' => '', 'r' => '', 's' => ''],
                '', '', '', '', '', ''
            ],
            [
                ['chainId' => null, 'nonce' => '04', 'maxPriorityFeePerGas' => '03f5476a00', 'maxFeePerGas' => '027f4b', 'gasLimit' => '1a8c8adfbe1c59e8b58cc0d515f07b7225f51c72', 'to' => '2a45907d1bef7c00', 'value' => '', 'data' => '', 'accessList' => [], 'v' => '', 'r' => '', 's' => ''],
                '04', '03f5476a00', '027f4b', '1a8c8adfbe1c59e8b58cc0d515f07b7225f51c72', '2a45907d1bef7c00', ''
            ],
        ];
    }

    /**
     * @dataProvider getTransactionData
     */
    public function testGetRaw($txid, $unsigned, $signed, $privateKey, $chainId, $nonce, $maxPriorityFeePerGas, $maxFeePerGas, $gasLimit, $to, $value, $data)
    {
        $transaction = new EIP1559Transaction($nonce, $maxPriorityFeePerGas, $maxFeePerGas, $gasLimit, $to, $value, $data);
        $this->assertSame($signed, $transaction->getRaw($privateKey, $chainId));
    }

    /**
     * @dataProvider getTransactionData
     */
    public function testGetUnsigned($txid, $unsigned, $signed, $privateKey, $chainId, $nonce, $maxPriorityFeePerGas, $maxFeePerGas, $gasLimit, $to, $value, $data)
    {
        $transaction = new EIP1559Transaction($nonce, $maxPriorityFeePerGas, $maxFeePerGas, $gasLimit, $to, $value, $data);
        $this->assertSame($unsigned, $transaction->getUnsigned($chainId));
    }

    /**
     * @dataProvider getTransactionData
     */
    public function testHash($txid, $unsigned, $signed, $privateKey, $chainId, $nonce, $maxPriorityFeePerGas, $maxFeePerGas, $gasLimit, $to, $value, $data)
    {
        $transaction = new EIP1559Transaction($nonce, $maxPriorityFeePerGas, $maxFeePerGas, $gasLimit, $to, $value, $data);
        $this->assertSame($txid, $transaction->hash($chainId));
    }

    public static function getTransactionData(): array
    {
        return [
            [
                '4fa4b9bd743ee70bb1dc109cadaa5985d5e9461e793405261daeff7bb6419865', //txid
                '02f0010284b2d05e008506fc23ac00825208941a8c8adfbe1c59e8b58cc0d515f07b7225f51c728829a2241af62c000080c0', //unsigned
                '02f873010284b2d05e008506fc23ac00825208941a8c8adfbe1c59e8b58cc0d515f07b7225f51c728829a2241af62c000080c080a0dd32dc794af9a9085d6772c40656fc156a577570c6fd32f2a2d4126673373919a066cf1859672a9e6fdbb00ebd230bcfec66cac1c99f5c83598ecae6025d0e91f4',
                'b2f2698dd7343fa5afc96626dee139cb92e58e5d04e855f4c712727bf198e898', 1, '02', 'b2d05e00', '6fc23ac00', '5208', '1a8c8adfbe1c59e8b58cc0d515f07b7225f51c72', '29a2241af62c0000', ''
            ],
            [
                'f470e5422f3dc01bca43f818f03de3eea876328f8a7d1c9c4fe05410f6fc3bb6', //txid
                '02f0380284b2d05e008506fc23ac00825208941a8c8adfbe1c59e8b58cc0d515f07b7225f51c728829a2241af62c000080c0', //unsigned
                '02f873380284b2d05e008506fc23ac00825208941a8c8adfbe1c59e8b58cc0d515f07b7225f51c728829a2241af62c000080c080a0b8ec74b95d0d8ebe4b4c7e9d5a8af16e06a7d636d20b2c632633a2abe3a0f5cca005971b1f3ec9b498d8d7b17b312477d0b1fd2232ca4b82e9c34fc43d33e5f196',
                'b2f2698dd7343fa5afc96626dee139cb92e58e5d04e855f4c712727bf198e898', 56, '02', 'b2d05e00', '6fc23ac00', '5208', '1a8c8adfbe1c59e8b58cc0d515f07b7225f51c72', '29a2241af62c0000', ''
            ],
            [
                '950b552d191d26ea7b2dbaf53e59b2f82177b1924f8ac06715a0fe0f058f1c20', //txid
                '02f4380284b2d05e008506fc23ac00825208941a8c8adfbe1c59e8b58cc0d515f07b7225f51c728829a2241af62c00008401232213c0', //unsigned
                '02f877380284b2d05e008506fc23ac00825208941a8c8adfbe1c59e8b58cc0d515f07b7225f51c728829a2241af62c00008401232213c001a05bc0caa25dd8e23adf3f79f8dbe1237e0f22980a3b15f00bbe9f5a19dd23a70ca002e9a353c9b155b0e7e796b5f966bc6dd53deafea9990398a069d93278e50644',
                'b2f2698dd7343fa5afc96626dee139cb92e58e5d04e855f4c712727bf198e898', 56, '02', 'b2d05e00', '6fc23ac00', '5208', '1a8c8adfbe1c59e8b58cc0d515f07b7225f51c72', '29a2241af62c0000', '1232213'
            ]
        ];
    }

    public function testGetRawBadChainId()
    {
        $this->expectException(RuntimeException::class);
        $transaction = new EIP1559Transaction('04', '03f5476a00', '027f4b', '1a8c8adfbe1c59e8b58cc0d515f07b7225f51c72', '2a45907d1bef7c00', '');
        $transaction->getRaw ('b2f2698dd7343fa5afc96626dee139cb92e58e5d04e855f4c712727bf198e898', -1);
    }

    public function testBadPrivateKey()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Incorrect private key');
        $transaction = new EIP1559Transaction();
        $transaction->getRaw('');
    }

}
