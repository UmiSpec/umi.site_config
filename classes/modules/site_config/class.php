<?php
	class site_config extends def_module {
        public function __construct() {
			parent::__construct();

			if(cmsController::getInstance()->getCurrentMode() == "admin") {
				$this->__loadLib("__admin.php");
				$this->__implement("__site_config");
			} else {
				$this->__loadLib("__custom.php");
				$this->__implement("__custom_site_config");
			}
		}

        public function get() {
            $cms = cmsController::getInstance();

            $domain_id = $cms->getCurrentDomain()->getId();
            $lang_id = $cms->getCurrentLang()->getId();

            $folder = CURRENT_WORKING_DIR . '/sys-temp/siteconfigcache/';
            $path = $folder . md5(sprintf('%d - %d', $domain_id, $lang_id)) . '.php';
            if(!is_dir($folder)) mkdir($folder, 0777, true);

            if(!is_file($path)) {
                $config = $this->getConfig();

                if(!$config)
                    return;

                $wrapper = translatorWrapper::get($config);
                $wrapper->isFull = true;

                $data = $wrapper->translate($config);

                file_put_contents($path, serialize($data));
            } else {
                $data = unserialize(file_get_contents($path));
            }

            return array('config' => $data);
        }

        public function getConfig($domain_id = false, $lang_id = false) {
            if(!$domain_id || !$lang_id) {
                $cms = cmsController::getInstance();

                if(!$lang_id) $lang_id = $cms->getCurrentLang()->getId();
                if(!$domain_id) $domain_id = $cms->getCurrentDomain()->getId();
            }

            $sel = new selector('objects');
            $sel->types('object-type')->id(umiObjectTypesCollection::getInstance()->getBaseType('site_config', 'site_config'));
            $sel->where('domain_id')->equals($domain_id);
            $sel->where('lang_id')->equals($lang_id);
            $sel->limit(0, 1);

            return $sel->length() > 0 ? $sel->result[0] : false;
        }
	};
?>