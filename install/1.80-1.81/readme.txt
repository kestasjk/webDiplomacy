Changelog
---------

- Added Web Push (PWA) notifications: new wD_PushSubscriptions table storing browser push
  subscriptions. Users get OS notifications when a turn processes or they receive an in-game
  message. Requires VAPID keys in config.php ($vapidPublicKey/$vapidPrivateKey/$vapidSubject),
  the minishlink/web-push composer package (composer update), and the php gmp extension.
  Initially gated to the userIDs listed in Config::$pushEnabledUserIDs.
