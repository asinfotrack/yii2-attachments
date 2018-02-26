<?php
use asinfotrack\yii2\attachments\Module;

return [

	'defaultRoute'=>'attachment/index',

	'fileInputCallback'=>[Module::className(), 'defaultFileInput'],

];
