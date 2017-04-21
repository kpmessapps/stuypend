<?php

return [
  'gcm' => [
      'priority' => 'normal',
      'dry_run' => false
  ],
  'fcm' => [
        'priority' => 'high',
        'dry_run' => false
  ],
  'apn' => [
      'certificate' => __DIR__ . '/iosCertificates/apns-dev.pem',
      'passPhrase' => '',
      'dry_run' => true     // sandbox => true , production => false
  ]
];