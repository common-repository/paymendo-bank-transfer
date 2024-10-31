<?php

namespace Grilabs\Paymendo\BankTransfer;

if (!class_exists('Grilabs\Paymendo\BankTransfer\PBT_Bank')) {
    class PBT_Bank
    {
        public $bank_name;
        public $slug;
        public $logo;

        /**
         * @param $bank_name
         * @param $slug
         * @param $logo
         */
        public function __construct($bank_name, $slug, $logo)
        {
            $this->bank_name = $bank_name;
            $this->slug = $slug;
            $this->logo = $logo;
        }

        /**
         * @return mixed
         */
        public function getBankName()
        {
            return $this->bank_name;
        }

        /**
         * @param mixed $bank_name
         */
        public function setBankName($bank_name)
        {
            $this->bank_name = $bank_name;
        }

        /**
         * @return mixed
         */
        public function getSlug()
        {
            return $this->slug;
        }

        /**
         * @param mixed $slug
         */
        public function setSlug($slug)
        {
            $this->slug = $slug;
        }

        /**
         * @return mixed
         */
        public function getLogo()
        {
            return $this->logo;
        }

        /**
         * @param mixed $logo
         */
        public function setLogo($logo)
        {
            $this->logo = $logo;
        }

    }
}