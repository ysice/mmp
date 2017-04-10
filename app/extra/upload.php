<?php
return [
    'driver' => 'aliyun',
    /** limit file size,unit:byte,if the value equal 0 the file size will not restricted.  */
    'size' => 1024 * 1024,
    /** allow file extension.if is empty the file extension has no limit. */
    'ext'  => ['jpg', 'png', 'gif'],
    /** allow file type by file mime.if is empty the file type has no limit. */
    'type' => ['image/png', 'image/gif'],
    'path' => './images/',
    'default' => [
        /** access url */
        'remote_url' => 'http://xx.com/public/',
    ],
    'qiniu' => [
        'access_key' => '1',
        'secret_key' => '2',
        'bucket' => '3',
        /** access url */
        'remote_url' => '4',
    ],
    'aliyun' => [
        /** Internal net url or External net url */
        'oss_server' => '5',
        'access_key_id' => '6',
        'access_key_secret' => '7',
        'bucket' => '8',
        /** access url */
        'remote_url' => '9',
    ],
];