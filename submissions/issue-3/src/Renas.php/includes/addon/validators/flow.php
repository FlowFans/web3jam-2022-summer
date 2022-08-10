<?php
namespace validator;
################################################
# 本脚本不允许外部直接访问
################################################
if (!defined('_EXTERNAL')) die(header("Location: /"));

################################################
#flow地址验证
################################################

class flow
{
    public function isAddress(string $address): bool
    {
        // See: https://github.com/ethereum/web3.js/blob/7935e5f/lib/utils/utils.js#L415
        if ($this->matchesPattern($address)) {
            return $this->isAllSameCaps($address) ?: $this->isValidChecksum($address);
        }

        return false;
    }

    protected function matchesPattern(string $address): int
    {
        return preg_match('/^(0x)?[0-9a-f]+$/i', $address);
    }

    protected function isAllSameCaps(string $address): bool
    {
        return preg_match('/^(0x)?[0-9a-f]+$/', $address) || preg_match('/^(0x)?[0-9A-F]+$/', $address);
    }

    protected function isValidChecksum($address)
    {
        $address = str_replace('0x', '', $address);
        $hash = \vendor\Keccak::hash(strtolower($address), 256);

        // See: https://github.com/web3j/web3j/pull/134/files#diff-db8702981afff54d3de6a913f13b7be4R42
        for ($i = 0; $i < strlen($address); $i++ ) {
            if (ctype_alpha($address[$i])) {
                // Each uppercase letter should correlate with a first bit of 1 in the hash char with the same index,
                // and each lowercase letter with a 0 bit.
                $charInt = intval($hash[$i], 16);

                if ((ctype_upper($address[$i]) && $charInt <= 7) || (ctype_lower($address[$i]) && $charInt > 7)) {
                    return false;
                }
            }
        }

        return true;
    }
}