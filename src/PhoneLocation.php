<?php
namespace Ofan;

/**
 * 手机号码归属查询
 * $mobileNumber = new Ofan\PhoneLocation();
 * print_r($mobileNumber->find(15900000767));
 * print_r($mobileNumber->find(15900008755));
 * print_r($mobileNumber->find(15919252188));
 */
class PhoneLocation
{
    protected static $_fileHandle;

    protected static $_fileSize;

    protected static $version;
    protected static $offset;

    protected static $spList = [
        1 => '中国移动',
        2 => '中国联通',
        3 => '中国电信'
    ];

    public function __construct($file = null)
    {
        $file = realpath($file ?: __DIR__ . '/../data/mobile.dat');
        if (!self::$_fileHandle) {
            self::$_fileHandle = fopen($file, 'r');
        }
        if (!self::$_fileSize) {
            self::$_fileSize = filesize($file);
        }
        if (!self::$version) {
            fseek(self::$_fileHandle, 0);
            self::$version = unpack('N', fread(self::$_fileHandle, 4))[1];
        }
        if (!self::$offset) {
            fseek(self::$_fileHandle, 4);
            self::$offset = unpack('N', fread(self::$_fileHandle, 4))[1];
        }
    }
    /**
     * 查找手机号码归属地信息
     * @param int $phone
     * @return array|null
     */
    public function find($phone)
    {
        if (preg_match('/^1[34578]\d{9}$/', $phone)) {
            $value = $this->_find($phone);
        }
        return $value ?? null;
    }

    private function _find($phone)
    {
        //号码总数
        $total = (self::$_fileSize - self::$offset) / 7;
        $position = $leftPos = 0;
        $rightPos = $total;
        $telPrefix = substr($phone, 0, 7);
        while ($leftPos < $rightPos - 1) {
            $position = $leftPos + intval(($rightPos - $leftPos) / 2);
            $indexPos = ($position * 7) + self::$offset;
            fseek(self::$_fileHandle, $indexPos);
            $phone = unpack('N', fread(self::$_fileHandle, 4))[1];
            if ($phone < $telPrefix) {
                $leftPos = $position;
            } elseif ($phone > $telPrefix) {
                $rightPos = $position;
            } else {
                //查找运营商
                fseek(self::$_fileHandle, $indexPos + 4);
                $sp = unpack('C', fread(self::$_fileHandle, 1))[1];
                //查找详情
                fseek(self::$_fileHandle, $indexPos + 5);
                $itemPos = unpack('n', fread(self::$_fileHandle, 2))[1];
                fseek(self::$_fileHandle, $itemPos);
                $item = [];
                while (($tmp = unpack('N', fread(self::$_fileHandle, 4))[1]) !== 0) {
                    $item[] = $tmp;
                }

                $item = explode('|', $this->decode($item));
                $value = $this->phoneInfo($item, $sp);
                break;
            }
        }
        return $value ?? null;
    }


    private function phoneInfo($item, $type)
    {
        $type = self::$spList[$type];
        $data = [
            'sp' => $type,
            'province' => $item[1],
            'city' => $item[0],
            'zip_code' => $item[3],
            'area_code' => '0' . $item[2]
        ];
        return $data;
    }

    private function decode($str)
    {
        $utf = '';
        foreach ($str as $dec) {
            if ($dec < 128) {
                $utf .= chr($dec);
            } else if ($dec < 2048) {
                $utf .= chr(192 + (($dec - ($dec % 64)) / 64));
                $utf .= chr(128 + ($dec % 64));
            } else {
                $utf .= chr(224 + (($dec - ($dec % 4096)) / 4096));
                $utf .= chr(128 + ((($dec % 4096) - ($dec % 64)) / 64));
                $utf .= chr(128 + ($dec % 64));
            }
        }
        return $utf;
    }

    public function __destruct()
    {
        fclose(self::$_fileHandle);
    }
}
