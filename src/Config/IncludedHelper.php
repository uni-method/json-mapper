<?php declare(strict_types=1);

namespace UniMethod\JsonapiMapper\Config;

trait IncludedHelper
{
    /**
     * todo add check to avoid self loop
     * a,b.c,b.g.o =
     * [
     *  a => [],
     *  b => [
     *   c => [],
     *   g => [
     *    o => []
     *   ]
     *  ]
     * ]
     * @param string $included
     * @return array
     */
    protected function parseIncluded(string $included): array
    {
        if ($included === '') {
            return [];
        }
        $data = explode(',', $included);
        $result = [];
        array_walk($data, static function ($val) use (&$result) {
            $branch = static function (string $val) {
                return array_reduce(
                    array_reverse(explode('.', $val)),
                    static function ($carry, $item) {
                        $old = $carry;
                        $carry[$item] = $carry[0] ?? $carry;
                        return (is_array($old)) ? array_diff_key($carry, $old) : $carry;
                    },
                    []
                );
            };
            $result = array_merge_recursive($result, $branch($val));
        });
        return $result;
    }
}
