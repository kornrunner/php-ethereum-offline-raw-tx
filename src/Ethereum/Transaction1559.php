<?php


namespace kornrunner\Ethereum;

use kornrunner\Keccak;
use kornrunner\Secp256k1;
use kornrunner\Signature\Signature;
use RuntimeException;

class Transaction1559 extends Transaction
{
    protected $maxPriorityFee;
    protected $maxFee;
    protected $chainId = '';
    protected $yParity = '';

    public function __construct(string $nonce = '', string $maxPriorityFee = '', string $maxFee = '', string $gasLimit = '', string $to = '', string $value = '', string $data = '')
    {
        parent::__construct($nonce, '', $gasLimit, $to, $value, $data);
        $this->maxPriorityFee = $maxPriorityFee;
        $this->maxFee = $maxFee;
    }

    public function getInput(): array {
        return [
            'chainId' => $this->chainId,
            'nonce' => $this->nonce,
            'maxPriorityFee' => $this->maxPriorityFee,
	        'maxFee' => $this->maxFee,
            'gasLimit' => $this->gasLimit,
            'to' => $this->to,
            'value' => $this->value,
            'data' => $this->data,
            'accessList' => [],
            'yParity' => $this->yParity,
            'r' => $this->r,
            's' => $this->s,
        ];
    }

    public function getRaw(string $privateKey, int $chainId = 0): string {
        if ($chainId < 0) {
            throw new RuntimeException('ChainID must be positive');
        }
        if (strlen($privateKey) != 64) {
            throw new RuntimeException('Incorrect private key');
        }

        $this->sign($privateKey, $chainId);

        return $this->serialize();
    }

    private function serialize(): string {
        return '02' . $this->RLPencode($this->getInput());
    }

    private function sign(string $privateKey, int $chainId): void {
        $this->chainId = dechex($chainId);
        $hash = $this->hash();

        $secp256k1 = new Secp256k1();
        /**
         * @var Signature
         */
        $signed = $secp256k1->sign($hash, $privateKey);

        $this->yParity = $signed->getRecoveryParam();
        $this->r = $this->hexup(gmp_strval($signed->getR(), 16));
        $this->s = $this->hexup(gmp_strval($signed->getS(), 16));
    }

    private function hash(): string {
        $input = $this->getInput();

        unset($input['yParity']);
        unset($input['r']);
        unset($input['s']);

        $encoded = '02' . $this->RLPencode($input);
        return Keccak::hash(hex2bin($encoded), 256);
    }
}