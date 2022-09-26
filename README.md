# php-ethereum-offline-tx [![Tests](https://github.com/kornrunner/php-ethereum-offline-tx/actions/workflows/tests.yml/badge.svg?branch=master)](https://github.com/kornrunner/php-ethereum-offline-tx/actions/workflows/tests.yml) [![Coverage Status](https://coveralls.io/repos/github/kornrunner/php-ethereum-offline-raw-tx/badge.svg?branch=master)](https://coveralls.io/github/kornrunner/php-ethereum-offline-raw-tx?branch=master) [![Latest Stable Version](https://poser.pugx.org/kornrunner/ethereum-offline-raw-tx/v/stable)](https://packagist.org/packages/kornrunner/ethereum-offline-raw-tx)

Pure PHP Ethereum Offline Raw Transaction Signer

Ethereum raw transaction hash offline in PHP

## Installation

```sh
$ composer require kornrunner/ethereum-offline-raw-tx
```

## Usage

```php
use kornrunner\Ethereum\Transaction;

$nonce    = '04';
$gasPrice = '03f5476a00';
$gasLimit = '027f4b';
$to       = '1a8c8adfbe1c59e8b58cc0d515f07b7225f51c72';
$value    = '2a45907d1bef7c00';

$privateKey = 'b2f2698dd7343fa5afc96626dee139cb92e58e5d04e855f4c712727bf198e898';

$transaction = new Transaction ($nonce, $gasPrice, $gasLimit, $to, $value);
$transaction->getRaw ($privateKey);
// f86d048503f5476a0083027f4b941a8c8adfbe1c59e8b58cc0d515f07b7225f51c72882a45907d1bef7c00801ba0e68be766b40702e6d9c419f53d5e053c937eda36f0e973074d174027439e2b5da0790df3e4d0294f92d69104454cd96005e21095efd5f2970c2829736ca39195d8
```

With different `chainId`

```php
use kornrunner\Ethereum\Transaction;

$nonce    = '04';
$gasPrice = '03f5476a00';
$gasLimit = '027f4b';
$to       = '1a8c8adfbe1c59e8b58cc0d515f07b7225f51c72';
$value    = '2a45907d1bef7c00';
$chainId  = 1;

$privateKey = 'b2f2698dd7343fa5afc96626dee139cb92e58e5d04e855f4c712727bf198e898';

$transaction = new Transaction ($nonce, $gasPrice, $gasLimit, $to, $value);
$transaction->getRaw ($privateKey, $chainId);
// f86d048503f5476a0083027f4b941a8c8adfbe1c59e8b58cc0d515f07b7225f51c72882a45907d1bef7c008025a0db4efcc22a7d9b2cab180ce37f81959412594798cb9af7c419abb6323763cdd5a0631a0c47d27e5b6e3906a419de2d732e290b73ead4172d8598ce4799c13bda69
```

Create a EIP1559 transaction

```php
use kornrunner\Ethereum\EIP1559Transaction;

$nonce = '02';
$maxPriorityFeePerGas = 'b2d05e00';
$maxFeePerGas = '6fc23ac00';
$gasLimit = '5208';
$to = '1a8c8adfbe1c59e8b58cc0d515f07b7225f51c72';
$value = '29a2241af62c0000';
$data = '';
$chainId = 1;

$privateKey = 'b2f2698dd7343fa5afc96626dee139cb92e58e5d04e855f4c712727bf198e898';

$transaction = new EIP1559Transaction($nonce, $maxPriorityFeePerGas, $maxFeePerGas, $gasLimit, $to, $value, $data);

$rawTx = $transaction->getRaw($privateKey, $chainId);
// 02f873010284b2d05e008506fc23ac00825208941a8c8adfbe1c59e8b58cc0d515f07b7225f51c728829a2241af62c000080c080a0dd32dc794af9a9085d6772c40656fc156a577570c6fd32f2a2d4126673373919a066cf1859672a9e6fdbb00ebd230bcfec66cac1c99f5c83598ecae6025d0e91f4
```

## Crypto

[![Ethereum](https://user-images.githubusercontent.com/725986/61891022-0d0c7f00-af09-11e9-829f-096c039bbbfa.png) 0x9c7b7a00972121fb843af7af74526d7eb585b171][Ethereum]

[Ethereum]: https://etherscan.io/address/0x9c7b7a00972121fb843af7af74526d7eb585b171 "Donate with Ethereum"
