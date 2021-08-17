<?php

namespace kornrunner\Ethereum;

use kornrunner\Keccak;
use kornrunner\Secp256k1;
use RuntimeException;
use Web3p\RLP\RLP;
use kornrunner\Signature\Signature;

class Transaction {
    protected $nonce;
    protected $gasPrice;
    protected $gasLimit;
    protected $to;
    protected $value;
    protected $data;
    protected $r = '';
    protected $s = '';
    protected $v = '';

    public function __construct(string $nonce = '', string $gasPrice = '', string $gasLimit = '', string $to = '', string $value = '', string $data = '') {
        $this->nonce = $nonce;
        $this->gasPrice = $gasPrice;
        $this->gasLimit = $gasLimit;
        $this->to = $to;
        $this->value = $value;
        $this->data = $data;
    }

    public function getInput(): array {
        return [
            'nonce' => $this->nonce,
            'gasPrice' => $this->gasPrice,
            'gasLimit' => $this->gasLimit,
            'to' => $this->to,
            'value' => $this->value,
            'data' => $this->data,
            'v' => $this->v,
            'r' => $this->r,
            's' => $this->s,
        ];
    }

    public function getRaw(string $privateKey, int $chainId = 0): string {
        if ($chainId < 0) {
            throw new RuntimeException('ChainID must be positive');
        }

        $this->v = '';
        $this->r = '';
        $this->s = '';

        if (strlen($privateKey) != 64) {
            throw new RuntimeException('Incorrect private key');
        }

        $this->sign($privateKey, $chainId);

        return $this->serialize();
    }

    private function serialize(): string {
        return $this->RLPencode($this->getInput());
    }

    private function sign(string $privateKey, int $chainId): void {
        $hash      = $this->hash($chainId);

        $secp256k1 = new Secp256k1();
        /**
         * @var Signature
         */
        $signed    = $secp256k1->sign($hash, $privateKey);

        $this->r   = $this->hexup(gmp_strval($signed->getR(), 16));
        $this->s   = $this->hexup(gmp_strval($signed->getS(), 16));
        $this->v   = dechex ((int) $signed->getRecoveryParam () + 27 + ($chainId ? $chainId * 2 + 8 : 0));
    }

    private function hash(int $chainId): string {
        $input = $this->getInput();

        if ($chainId > 0) {
            $input['v'] = dechex($chainId);
            $input['r'] = '';
            $input['s'] = '';
        } else {
            unset($input['v']);
            unset($input['r']);
            unset($input['s']);
        }

        $encoded = $this->RLPencode($input);

        return Keccak::hash(hex2bin($encoded), 256);
    }

    private function RLPencode(array $input): string {
        $rlp  = new RLP;

        $data = [];
        foreach ($input as $item) {
            $value  = strpos ($item, '0x') !== false ? substr ($item, strlen ('0x')) : $item;
            $data[] = $value ? '0x' . $this->hexup($value) : '';
        }

        return $rlp->encode($data);
    }

    private function hexup(string $value): string {
        return strlen ($value) % 2 === 0 ? $value : "0{$value}";
    }

}
