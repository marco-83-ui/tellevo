<?php

namespace App\Traits;

use Illuminate\Support\Str;
use Exception;
use App\Base\Constants\Setting\Settings;

trait BeneficaryTrait
{

        /*
        Below is an integration flow on how to use Cashfree's payouts.
        Please go through the payout docs here: https://dev.cashfree.com/payouts
        The following script contains the following functionalities :
            1.getToken() -> to get auth token to be used in all following calls.
            2.getBeneficiary() -> to get beneficiary details/check if a beneficiary exists
            3.createBeneficiaryEntity() -> to create beneficiaries
            4.requestTransfer() -> to create a payout transfer
            5.getTransferStatus() -> to get payout transfer status.
        All the data used by the script can be found in the below assosciative arrays. This includes the clientId, clientSecret, Beneficiary object, Transaction Object.
        You can change keep changing the values in the config file and running the script.
        Please enter your clientId and clientSecret, along with the appropriate enviornment, beneficiary details and request details
        */

    public $clientId;
    public $clientSecret;



    private $env = 'test';

    private $baseUrls = array(
            'prod' => 'https://payout-api.cashfree.com',
            'test' => 'https://payout-gamma.cashfree.com',
        );

    private $urls = array(
            'auth' => '/payout/v1/authorize',
            'getBene' => '/payout/v1/getBeneficiary/',
            'addBene' => '/payout/v1/addBeneficiary',
            'requestTransfer' => '/payout/v1/requestTransfer',
            'getTransferStatus' => '/payout/v1/getTransferStatus?transferId='
        );

    // private $beneficiary = array(
    //     'beneId' => 'JOHN180555',
    //     'name' => 'jhon doe',
    //     'email' => 'johndoe@cashfree.com',
    //     'phone' => '9876543210',
    //     'bankAccount' => '000890289871770',
    //     'ifsc' => 'SCBL0036078',
    //     'address1' => 'address1',
    //     'city' => 'bangalore',
    //     'state' => 'karnataka',
    //     'pincode' => '560001',
    // );

    // private $transfer = array(
    //     'beneId' => 'JOHN180555',
    //     'amount' => '1.00',
    //     'transferId' => 'DEC5555',
    // );



    private $baseurl = 'https://payout-gamma.cashfree.com';
    
    private $production_base_url = 'https://payout-api.cashfree.com';


    public function create_header($token)
    {
        $clientId = get_settings(Settings::CASH_FREE_TEST_CLIENT_ID_FOR_PAYOUT);
        $clientSecret = get_settings(Settings::CASH_FREE_TEST_CLIENT_SECRET_FOR_PAYOUT);

        if(get_settings(Settings::CASH_FREE_ENVIRONMENT)=='production'){
        // $clientId = 'CF53232C19371VA55OA211PLM5G';
        // $clientSecret = '9c71a5f0e6e4a666ab3abc077e17a6aa14ebe316';
        $clientId = get_settings(Settings::CASH_FREE_PRODUCTION_CLIENT_ID_FOR_PAYOUT);
        $clientSecret = get_settings(Settings::CASH_FREE_PRODUCTION_CLIENT_SECRET_FOR_PAYOUT);

        }
        $header = array(
                "X-Client-Id: $clientId",
                "X-Client-Secret: $clientSecret",
                "Content-Type: application/json",
            );

        $headers = $header;
        if (!is_null($token)) {
            array_push($headers, 'Authorization: Bearer '.$token);
        }
        return $headers;
    }

    public function post_helper($action, $data, $token)
    {
        $baseurl = $this->baseurl;

        if(get_settings(Settings::CASH_FREE_ENVIRONMENT=='production')){
        $baseurl = $this->production_base_url;
        }

        $urls = $this->urls;


        $finalUrl = $baseurl.$urls[$action];
        $headers = $this->create_header($token);

        // if($action == 'requestTransfer') {

        //     echo "<pre>";
        //     print_r($action);

        //     echo "<pre>";
        //     print_r($data);

        //     echo "<pre>";
        //     print_r($token);

        //     die();

        // }


        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_URL, $finalUrl);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if (!is_null($data)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        $r = curl_exec($ch);

        if (curl_errno($ch)) {
            $this->throwCustomException(curl_error($ch));

            // print('error in posting');
                // print(curl_error($ch));
                // die();
        }
        curl_close($ch);
        $rObj = json_decode($r, true);
        if ($rObj['status'] != 'SUCCESS' || $rObj['subCode'] != '200') {
            throw new Exception('incorrect response: '.$rObj['message']);
        }
        return $rObj;
    }

    public function get_helper($finalUrl, $token)
    {
        $headers = $this->create_header($token);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $finalUrl);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $r = curl_exec($ch);

        if (curl_errno($ch)) {
            $this->throwCustomException(curl_error($ch));

            // print('error in posting');
                // print(curl_error($ch));
                // die();
        }
        curl_close($ch);

        $rObj = json_decode($r, true);
        if ($rObj['status'] != 'SUCCESS' || $rObj['subCode'] != '200') {
            throw new Exception('incorrect response: '.$rObj['message']);
        }
        return $rObj;
    }

    //get auth token
    public function getToken()
    {
        try {
            $response = $this->post_helper('auth', null, null);
            return $response['data']['token'];
        } catch (Exception $ex) {
            $this->throwCustomException($ex->getMessage());

            // error_log('error in getting token');
                // error_log($ex->getMessage());
                // die( $ex->getMessage() );
        }
    }

    //get beneficiary details
    // public function getBeneficiary($token){
    //     try{
    //         // global $baseurl, $urls, $beneficiary;

    //         $baseurl = $this->baseurl;
    //         $urls = $this->urls;
    //         $beneficiary= $this->beneficiary;

    //         echo "<pre>";
    //         print_r( $beneficiary );

    //         die();

    //         $beneId = $beneficiary['beneId'];
    //         $finalUrl = $baseurl.$urls['getBene'].$beneId;
    //         $response = $this->get_helper($finalUrl, $token);
    //         return true;
    //     }
    //     catch(Exception $ex){
    //         $msg = $ex->getMessage();
    //         if(strstr($msg, 'Beneficiary does not exist')) return false;
    //         error_log('error in getting beneficiary details');
    //         error_log($msg);
    //         die();
    //     }
    // }

    //add beneficiary
    public function addBeneficiary($beneficiary)
    {
        try {
            $beneficiary['beneId'] =  Str::random(40);

            $token = $this->getToken();

            $response = $this->post_helper('addBene', $beneficiary, $token);

            return $beneficiary['beneId'];
        } catch (Exception $ex) {
            $this->throwCustomException($ex->getMessage());
        }
    }

    //request transfer
    public function requestTransfer($transfer)
    {
        // dd($transfer);
        try {
            $token = $this->getToken();

            $response = $this->post_helper('requestTransfer', $transfer, $token);
        } catch (Exception $ex) {
            $this->throwCustomException($ex->getMessage());

            // $msg = $ex->getMessage();
                // error_log('error in requesting transfer');
                // error_log($msg);
                // die();
        }
    }

    //get transfer status
    public function getTransferStatus($transferId)
    {
        try {
            $token = $this->getToken();

            $baseurl = $this->baseurl;
            $urls= $this->urls;

            $finalUrl = $baseurl.$urls['getTransferStatus'].$transferId;
            $response = $this->get_helper($finalUrl, $token);

            return json_encode($response);
        } catch (Exception $ex) {
            $this->throwCustomException($ex->getMessage());

            // $msg = $ex->getMessage();
                // error_log('error in getting transfer status');
                // error_log($msg);
                // die();
        }
    }

    // //main execution
        // $token = getToken();

        // // $token = 'ab9JCVXpkI6ICc5RnIsICN4MzUIJiOicGbhJye.ab9JCSUVVQflEUBRVVPlVQQJiOiIWdzJCL3MzNxkzM1EjNxojI0FWaiwyNzMjM5MTNxYTM6ICc4VmIsISMzIjL2ETMuYDNucTNxIiOiAXaiwSZzxWYmpjIrNWZoNUZyVHdh52ZpNnIsgjN2MzN6ICZJRnb192YjFmIsIyRBxETQFTMyE0T1UTQ3gjTERTMDJzMyMTNGNkI6ICZJRnbllGbjJye.abk13Mgb2GQ5JvlEZMWBTlvlrq25IJntnwyplhgTf4H0Hnx1RvRL73oxP-8kRTuKg9';

        // if(!getBeneficiary($token)) addBeneficiary($token);
        // requestTransfer($token);
        // getTransferStatus($token);
}
