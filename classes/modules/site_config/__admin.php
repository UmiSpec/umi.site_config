<?php
	abstract class __site_config extends baseModuleAdmin {
        public function view() {
            $cms = cmsController::getInstance();

            $domain_id = $cms->getCurrentDomain()->getId();
            $lang_id = $cms->getCurrentLang()->getId();

            $config = $this->getConfig($domain_id, $lang_id);

            if(!$config)
                $config = $this->createConfig();

            if(!$config) {
                var_dump('Произошла системная ошибка');
                die();
            }

            $mode = (string) getRequest('param0');
            $inputData = array(
                'object'				=> $config,
                'allowed-object-types'	=> array('site_config', 'site_config')
            );

            if($mode == "do") {
                $object = $this->saveEditedObjectData($inputData);

                $folder = CURRENT_WORKING_DIR . '/sys-temp/siteconfigcache/';
                $path = $folder . md5(sprintf('%d - %d', $domain_id, $lang_id)) . '.php';
                if(!is_dir($folder)) mkdir($folder, 0777, true);

                $wrapper = translatorWrapper::get($object);
                $wrapper->isFull = true;

                $data = $wrapper->translate($object);

                file_put_contents($path, serialize($data));

                $this->chooseRedirect();
            }

            $this->setDataType("form");
            $this->setActionType("modify");

            $data = $this->prepareData($inputData, "object");

            $this->setData($data);
            return $this->doData();
        }

        public function createConfig() {
            $cms = cmsController::getInstance();

            $domain = $cms->getCurrentDomain();
            $lang = $cms->getCurrentLang();

            $objects = umiObjectsCollection::getInstance();

            $type_id = umiObjectTypesCollection::getInstance()->getBaseType('site_config', 'site_config');

            if(!$type_id)
                return false;

            $config_id = $objects->addObject(sprintf('%s - %s', $domain->getHost(), $lang->getPrefix()), $type_id);

            $config = false;

            if($config_id) {
                $config = $objects->getObject($config_id);

                $config->setValue('domain_id', $domain->getId());
                $config->setValue('lang_id', $lang->getId());
                $config->commit();
            }

            return $config;
        }
	};
?>
