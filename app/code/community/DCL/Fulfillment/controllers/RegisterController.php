<?php

/**
 * @author DCL
 */

class DCL_Fulfillment_RegisterController extends Mage_Core_Controller_Front_Action//Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $data = json_decode($this->getRequest()->getPost('data', null));
        if ($data->account_number == '') {
            $user_pw = Mage::helper('dcl_fulfillment/admin')->generateAPICredentialsForStore($data->store_id); 

            if (is_array($user_pw)) {
                $data->api_username = $user_pw[0];
                $data->api_key = $user_pw[1];
            }
        }

        $setup_store['data'] = json_encode($data);

        $ch = curl_init();

        curl_setopt($ch,CURLOPT_URL, "https://integrations.dclcorp.com/magentoapi/setup"); //set URL
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch,CURLOPT_POST, 1);
        curl_setopt($ch,CURLOPT_POSTFIELDS, $setup_store);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        //execute post
        $result = curl_exec($ch);

        //close connection
        curl_close($ch);

        $server_result = json_decode($result);

        if ($server_result && $server_result->error_code == 0) {

            $installer = new Mage_Eav_Model_Entity_Setup('core_setup');
            $installer->startSetup();

            $installer->setConfigData("dcl_fulfillment/general/account_number", $server_result->account_number, "stores", $data->store_id);

            if ($server_result->account_number == '') {
                $installer->setConfigData("dcl_fulfillment/general/efactory_username", "", "stores", $data->store_id);
                $installer->setConfigData("dcl_fulfillment/general/efactory_password", "", "stores", $data->store_id);
                $installer->setConfigData("dcl_fulfillment/general/api_username", "", "stores", $data->store_id);
                $installer->setConfigData("dcl_fulfillment/general/api_key", "", "stores", $data->store_id);
            }
            else {
                $input = json_decode($this->getRequest()->getPost('data', null));
                $installer->setConfigData("dcl_fulfillment/general/efactory_username", $input->efactory_username, "stores", $data->store_id);
                $installer->setConfigData("dcl_fulfillment/general/efactory_password", $input->efactory_password, "stores", $data->store_id);
            }

            $installer->endSetup();
        }
        header('Content-Type: application/json', true);
        ob_end_clean();
        echo $result;
    }
}

?>
