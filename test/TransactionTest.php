<?php

namespace kornrunner;

use kornrunner\Ethereum\EIP1559Transaction;
use kornrunner\Ethereum\Transaction;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class TransactionTest extends TestCase {

    /**
     * @dataProvider input
     */
    public function testGetInput ($expect, $nonce, $gasPrice, $gasLimit, $to, $value, $data) {
        $transaction = new Transaction ($nonce, $gasPrice, $gasLimit, $to, $value, $data);
        $this->assertSame($expect, $transaction->getInput());
    }

    public static function input (): array {
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
     * @dataProvider getRaw
     */
    public function testGetRaw ($expect, $privateKey, $chainId, $nonce, $gasPrice, $gasLimit, $to, $value, $data) {
        $transaction = new Transaction ($nonce, $gasPrice, $gasLimit, $to, $value, $data);
        $this->assertSame($expect, $transaction->getRaw($privateKey, $chainId));
    }

    public function testGetRawBadChainId () {
        $this->expectException(RuntimeException::class);
        $transaction = new Transaction ('04', '03f5476a00', '027f4b', '1a8c8adfbe1c59e8b58cc0d515f07b7225f51c72', '2a45907d1bef7c00', '');
        $transaction->getRaw ('b2f2698dd7343fa5afc96626dee139cb92e58e5d04e855f4c712727bf198e898', -1);
    }

    public static function getRaw (): array {
        return [
            [
                'f86d048503f5476a0083027f4b941a8c8adfbe1c59e8b58cc0d515f07b7225f51c72882a45907d1bef7c00801ba0e68be766b40702e6d9c419f53d5e053c937eda36f0e973074d174027439e2b5da0790df3e4d0294f92d69104454cd96005e21095efd5f2970c2829736ca39195d8',
                'b2f2698dd7343fa5afc96626dee139cb92e58e5d04e855f4c712727bf198e898', 0, '04', '03f5476a00', '027f4b', '1a8c8adfbe1c59e8b58cc0d515f07b7225f51c72', '2a45907d1bef7c00', ''
            ],
            [
                'f86d048503f5476a0083027f4b941a8c8adfbe1c59e8b58cc0d515f07b7225f51c72882a45907d1bef7c008025a0db4efcc22a7d9b2cab180ce37f81959412594798cb9af7c419abb6323763cdd5a0631a0c47d27e5b6e3906a419de2d732e290b73ead4172d8598ce4799c13bda69',
                'b2f2698dd7343fa5afc96626dee139cb92e58e5d04e855f4c712727bf198e898', 1, '04', '03f5476a00', '027f4b', '1a8c8adfbe1c59e8b58cc0d515f07b7225f51c72', '2a45907d1bef7c00', ''
            ],
            [
                'f86e048503f5476a0083027f4b941a8c8adfbe1c59e8b58cc0d515f07b7225f51c72882a45907d1bef7c00808193a05c206269d6d902591b5b37ec821be99f78f1375191b8a8fb5a9e7bd87cb33999a027ca8262e92e3a5d7d2682d0394587bd7362250f4313767aa1a656c075d09911',
                'b2f2698dd7343fa5afc96626dee139cb92e58e5d04e855f4c712727bf198e898', 56, '04', '03f5476a00', '027f4b', '1a8c8adfbe1c59e8b58cc0d515f07b7225f51c72', '2a45907d1bef7c00', ''
            ],
        ];
    }

    public function testBadPrivateKey () {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Incorrect private key');
        $transaction = new Transaction ();
        $transaction->getRaw('');
    }

    /**
     * @dataProvider getEIP1559Raw
     */
    public function testGetEIP1559Raw($expect, $privateKey, $chainId, $nonce, $maxPriorityFeePerGas, $maxFeePerGas, $gasLimit, $to, $value, $data) {
        $transaction = new EIP1559Transaction($nonce, $maxPriorityFeePerGas, $maxFeePerGas, $gasLimit, $to, $value, $data);
        $this->assertSame($expect, $transaction->getRaw($privateKey, $chainId));
    }

    public static function getEIP1559Raw(): array {
        return [
            [
                '02f873010284b2d05e008506fc23ac00825208941a8c8adfbe1c59e8b58cc0d515f07b7225f51c728829a2241af62c000080c080a0dd32dc794af9a9085d6772c40656fc156a577570c6fd32f2a2d4126673373919a066cf1859672a9e6fdbb00ebd230bcfec66cac1c99f5c83598ecae6025d0e91f4',
                'b2f2698dd7343fa5afc96626dee139cb92e58e5d04e855f4c712727bf198e898', 1, '02', 'b2d05e00', '6fc23ac00', '5208', '1a8c8adfbe1c59e8b58cc0d515f07b7225f51c72', '29a2241af62c0000', ''
            ],
            [
                '02f873380284b2d05e008506fc23ac00825208941a8c8adfbe1c59e8b58cc0d515f07b7225f51c728829a2241af62c000080c080a0b8ec74b95d0d8ebe4b4c7e9d5a8af16e06a7d636d20b2c632633a2abe3a0f5cca005971b1f3ec9b498d8d7b17b312477d0b1fd2232ca4b82e9c34fc43d33e5f196',
                'b2f2698dd7343fa5afc96626dee139cb92e58e5d04e855f4c712727bf198e898', 56, '02', 'b2d05e00', '6fc23ac00', '5208', '1a8c8adfbe1c59e8b58cc0d515f07b7225f51c72', '29a2241af62c0000', ''
            ],
            [
                '02f877380284b2d05e008506fc23ac00825208941a8c8adfbe1c59e8b58cc0d515f07b7225f51c728829a2241af62c00008401232213c001a05bc0caa25dd8e23adf3f79f8dbe1237e0f22980a3b15f00bbe9f5a19dd23a70ca002e9a353c9b155b0e7e796b5f966bc6dd53deafea9990398a069d93278e50644',
                'b2f2698dd7343fa5afc96626dee139cb92e58e5d04e855f4c712727bf198e898', 56, '02', 'b2d05e00', '6fc23ac00', '5208', '1a8c8adfbe1c59e8b58cc0d515f07b7225f51c72', '29a2241af62c0000', '1232213'
            ]
        ];
    }
}
