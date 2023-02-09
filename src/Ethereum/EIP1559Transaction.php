<?php

namespace kornrunner\Ethereum;

use kornrunner\Keccak;
use kornrunner\Secp256k1;
use RuntimeException;
use kornrunner\RLP\RLP;
use kornrunner\Signature\Signature;

class EIP1559Transaction
{
    protected $chainId;
    protected $nonce;
    protected $maxPriorityFeePerGas;
    protected $maxFeePerGas;
    protected $gasLimit;
    protected $to;
    protected $value;
    protected $data;
    protected $accessList;
    protected $r = '';
    protected $s = '';
    protected $v = '';
    protected $txType = '02';

    public function __construct(string $nonce = '', string $maxPriorityFeePerGas = '', string $maxFeePerGas = '', string $gasLimit = '', string $to = '', string $value = '', string $data = '')
    {
        $this->nonce = $nonce;
        $this->maxPriorityFeePerGas = $maxPriorityFeePerGas;
        $this->maxFeePerGas = $maxFeePerGas;
        $this->gasLimit = $gasLimit;
        $this->to = $to;
        $this->value = $value;
        $this->data = $data;
        $this->accessList = [];
    }

    public function getInput(): array
    {
        return [
            'chainId' => $this->chainId,
            'nonce' => $this->nonce,
            'maxPriorityFeePerGas' => $this->maxPriorityFeePerGas,
            'maxFeePerGas' => $this->maxFeePerGas,
            'gasLimit' => $this->gasLimit,
            'to' => $this->to,
            'value' => $this->value,
            'data' => $this->data,
            'accessList' => $this->accessList,
            'v' => $this->v,
            'r' => $this->r,
            's' => $this->s,
        ];
    }

    public function getRaw(string $privateKey, int $chainId = 0): string
    {
        if ($chainId < 0) {
            throw new RuntimeException('ChainID must be positive');
        }

        $this->chainId = dechex($chainId);
        $this->v = '';
        $this->r = '';
        $this->s = '';

        if (strlen($privateKey) != 64) {
            throw new RuntimeException('Incorrect private key');
        }

        $this->sign($privateKey);

        return $this->serialize();
    }

    private function serialize(): string
    {
        return $this->txType . $this->RLPencode($this->getInput());
    }

    private function sign(string $privateKey): void
    {
        $hash = $this->hash();

        $secp256k1 = new Secp256k1();
        /**
         * @var Signature
         */
        $signed = $secp256k1->sign($hash, $privateKey);
        $this->r = $this->hexup(gmp_strval($signed->getR(), 16));
        $this->s = $this->hexup(gmp_strval($signed->getS(), 16));
        $this->v = dechex((int)$signed->getRecoveryParam());
    }

    private function hash(): string
    {
        $input = $this->getInput();

        unset($input['v']);
        unset($input['r']);
        unset($input['s']);

        $encoded = $this->RLPencode($input);

        return Keccak::hash(hex2bin($this->txType . $encoded), 256);
    }

    private function RLPencode(array $input): string
    {
        $rlp = new RLP;

        $data = [];
        foreach ($input as $item) {
            if (is_array($item)) {
                $data[] = $item;
                continue;
            }

            $value = strpos($item, '0x') !== false ? substr($item, strlen('0x')) : $item;
            $data[] = $value ? '0x' . $this->hexup($value) : '';
        }
        return $rlp->encode($data);
    }

    private function hexup(string $value): string
    {
        return strlen($value) % 2 === 0 ? $value : "0{$value}";
    }

}
