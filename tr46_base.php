<?php

// the base class to extend from if your Last Mile Delivery API has additional things you need to test
class TR46_Base {

  // the main call
  function main() {
    $this->test_config();
    $this->intro();
    $this->readyornot();
  }

  // Set the base URL of your API here. Something like http://www.yourlmdsystem.com/api/
  function config_base_url() {
    $base_url="";
    return $base_url;
  }

  // if your API requires a token, set the token endpoint here.
  function config_token_endpoint() {
    $endpoint=""; // It could be something like "session"
    return $endpoint;
  }

  // if your API requires a token, set the token endpoint here.
  function config_create_order_endpoint() {
    $endpoint=""; // It could be something like "order/create"
    return $endpoint;
  }

  // if your API requires a token, set the token endpoint here.
  function config_get_status_endpoint() {
    $endpoint=""; // It could be something like "order/status"
    return $endpoint;
  }

  // if your API requires a token, set the token endpoint here.
  function config_cancel_order_endpoint() {
    $endpoint=""; // It could be something like "order/cancel"
    return $endpoint;
  }

  // A boilerplate for token generation for APIs with tokenisation. Edit as required.
  function generate_token() {
    $url=$this->config_base_url().$this->config_token_endpoint();
    $logincreds = array(
      "user"=>array(
        "username"=>"foo@bar.com",
        "password"=>"password"
      )
    );
    $data_string = json_encode($logincreds);
    $response=$this->send($url,"token","POST",$data_string,$access_token);

    $headers=array();

    $data=explode("\n",$response);

    $headers['status']=$data[0];

    array_shift($data);

    foreach($data as $part){
        $middle=explode(":",$part);
        $headers[trim($middle[0])] = trim($middle[1]);
    }

    $token = $headers["Authorization"];

    return $token;
  }

  // our function to read from the command line
  function read_stdin() {
    $fr=fopen("php://stdin","r");   // open our file pointer to read from stdin
    $input = fgets($fr,128);        // read a maximum of 128 characters
    $input = rtrim($input);         // trim any trailing spaces.
    fclose ($fr);                   // close the file handle
    return $input;                  // return the text entered
  }

  // coz we needed a multi-dimensional array_key() method
  function array_keys_multi(array $array) {
      $keys = array();

      foreach ($array as $key => $value) {
          $keys[] = $key;

          if (is_array($value)) {
              $keys = array_merge($keys, $this->array_keys_multi($value));
          }
      }

      return $keys;
  }

  // coz we need to test for valid Date
  function is_date($date) {
      $d = DateTime::createFromFormat('Y-m-d H:i:s', $date);
      return $d && $d->format('Y-m-d H:i:s') === $date;
  }

  // A boilerplate for curl commands. Takes in the endpoint url, a default/token, a POST or GET method and an access token if needed
  function send($url, $action="default", $method="POST", $data_string=null, $access_token=null) {
    $ch = curl_init($url);
    if($action=="default"):
      curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
      curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_HTTPHEADER, array(
          'Authorization: '.$access_token,
          'Content-Type: application/json')
      );
      $response = curl_exec($ch);
    elseif($action=="token"):
      curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
      curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_HEADER, true);
      curl_setopt($ch, CURLOPT_HTTPHEADER, array(
          'Content-Type: application/json',
          'Content-Length: ' . strlen($data_string))
      );
      $response = curl_exec($ch);
    else:
      $response="Error: Unrecognised call.";
    endif;
    curl_close($ch);
    return $response;
  }

  // coz I decided to open source this and make it work for everyone interested to comply to TR46. Please buy me coffee. Or a bungalow.
  function test_config() {
    if ($this->config_base_url()==""):
      echo "You'll need to create tr46_yoursystem.php, override config_base_url() method and set the \$base_url before continuing.\n";
      echo "If your API requires a token, set the endpoint in your overridden config_token_endpoint().\n";
      exit;
    endif;
  }

  // present ascii art and some information about the test.
  function intro() {
    $ascii_art ="
    .___________..______      _  _       __      .___________. _______     _______.___________. __  ___  __  .___________.
    |           ||   _  \    | || |     / /      |           ||   ____|   /       |           ||  |/  / |  | |           |
    `---|  |----`|  |_)  |   | || |_   / /_      `---|  |----`|  |__     |   (----`---|  |----`|  '  /  |  | `---|  |----`
        |  |     |      /    |__   _| | '_ \         |  |     |   __|     \   \       |  |     |    <   |  |     |  |
        |  |     |  |\  \----.  | |   | (_) |        |  |     |  |____.----)   |      |  |     |  .  \  |  |     |  |
        |__|     | _| `._____|  |_|    \___/         |__|     |_______|_______/       |__|     |__|\__\ |__|     |__|

    ";
    echo $ascii_art."\n";
    echo "        Copyright (c) Hazrul Azhar Jamari. Website: https://www.hazrulazhar.com. Github: https://github.com/abanghazrul\n\n";
    echo "\nHello. This testkit audits your API against the TR46:2016 standards.\n\n";

    echo "These are the tests that we will be doing.:\n\n";

    echo "1. Create Delivery Order.\n";
    echo "2. Get Delivery Status.\n";
    echo "3. Cancel Delivery Order.\n\n";

    echo "You will be performing these 3 actions on the command line. An output showing the request and response will be shown.\n";
    echo "The auditor will review the JSON request and response.\n";
    echo "To assist, this software will test mandatory fields and expected data type, and display pass or failure.\n\n";
  }

  // coz you might want to take the time to understand what's going on before starting.
  function readyornot() {
    // indicate intent
    echo "Ready to start test? (y/n) [Default: y]: ";

    // the script will wait here until the user has entered something and hit ENTER
    $ready = $this->read_stdin();

    if($ready=="y" or $ready==""):
      echo "Woohoo!!....\n\n";
      $this->start();
    elseif($ready=="n"):
      echo "See you later then.\n\n";
      exit;
    else:
      echo "That's not a valid answer. Try again... \n\n";
      $this->readyornot();
    endif;
  }

  // starting the TestKit
  function start() {
    $token=null;
    if($this->config_token_endpoint()!=""):
      $token=$this->generate_token();
      echo "Token created!...\n\n";
    endif;

    echo "Choose a test:\n";
    echo "==============\n";
    echo "1. Create Delivery Order.\n";
    echo "2. Get Delivery Status.\n";
    echo "3. Cancel Delivery Order.\n\n";
    echo "Choice: ";
    $input = $this->read_stdin();

    if($input==1):
      echo "You chose to Create a Delivery Order.\n\n";
      $this->create_delivery_order($token);
    elseif($input==2):
      echo "You chose to Get Delivery Status.\n\n";
      $this->get_delivery_status($token);
    elseif($input==3):
      echo "You chose to Cancel Delivery Order.\n\n";
      $this->cancel_order($token);
    else:
      echo "You have fat fingers. Try again!...\n\n";
      $this->start();
    endif;
  }

  // Create Order
  function create_delivery_order($token) {

    echo "Choose an option.\n";
    echo "=================\n\n";
    echo "1. Create an order manually (Best if your API only uses mandatory fields).\n";
    echo "2. Use boilerplate ready order (Use this if you utilise additional fields).\n\n";
    echo "Choice: ";
    $input=$this->read_stdin();

    echo "\n";

    if($input==1):
      $eOrderId="TEST-".sha1(date("Ymd H:i:s"));
      echo "eOrderId: ".$eOrderId."\n";
      echo "Sender Name: ";
      $sender_name=$this->read_stdin();
      echo "Sender Contact Number: ";
      $sender_number=$this->read_stdin();
      echo "Sender Company Name: ";
      $sender_company=$this->read_stdin();
      echo "Sender Email: ";
      $sender_email=$this->read_stdin();
      echo "Sender Address Line 1: ";
      $sender_addrress=$this->read_stdin();
      echo "Sender Address Line 2: ";
      $sender_addrress2=$this->read_stdin();
      echo "Sender City: ";
      $sender_city=$this->read_stdin();
      echo "Sender Country: ";
      $sender_country=$this->read_stdin();
      echo "Sender Postal Code: ";
      $sender_postcode=$this->read_stdin();

      echo "Receiver Name: ";
      $receiver_name=$this->read_stdin();
      echo "Receiver Contact Number: ";
      $receiver_number=$this->read_stdin();
      echo "Receiver Company Name: ";
      $receiver_company=$this->read_stdin();
      echo "Receiver Email: ";
      $receiver_email=$this->read_stdin();
      echo "Receiver Address Line 1: ";
      $receiver_addrress=$this->read_stdin();
      echo "Receiver Address Line 2: ";
      $receiver_addrress2=$this->read_stdin();
      echo "Receiver City: ";
      $receiver_city=$this->read_stdin();
      echo "Receiver Country: ";
      $receiver_country=$this->read_stdin();
      echo "Receiver Postal Code: ";
      $receiver_postcode=$this->read_stdin();

      echo "Pickup Date/Time (Enter a date in format like `YYYY-MM-DDTHH:II:SS`): ";
      $pickup_time=$this->read_stdin();

      echo "Activate Request Date/Time? (y/n) [Default: n]: ";
      $activate_request_time=$this->read_stdin();

      if($activate_request_time=="y"):
        echo "Request Date/Time (Enter a date in format like `YYYY-MM-DDTHH:II:SS`): ";
        $request_time=$this->read_stdin();
      endif;

      echo "Parcel Description: ";
      $parcel_description=$this->read_stdin();
      echo "Order Remarks: ";
      $parcel_remarks=$this->read_stdin();

      echo "Activate Parcels Info? (y/n) [Default: n]: ";
      $activate_parcels_info=$this->read_stdin();

      if($activate_parcels_info=="y"):
        echo "Length: ";
        $parcel_length=$this->read_stdin();
        echo "Height: ";
        $parcel_height=$this->read_stdin();
        echo "Width: ";
        $parcel_width=$this->read_stdin();
        echo "Weight: ";
        $parcel_weight=$this->read_stdin();
        echo "Declared Currency: ";
        $parcel_declared_currency=$this->read_stdin();
        echo "Declared Value: ";
        $parcel_declared_value=$this->read_stdin();
      endif;

      echo "Activate Merchant Info? (y/n) [Default: n]: ";
      $activate_merchant_info=$this->read_stdin();

      if($activate_merchant_info=="y"):
        echo "eStoreId: ";
        $estore_id=$this->read_stdin();
        echo "merchantId: ";
        $merchant_id=$this->read_stdin();
      endif;

      echo "Activate Order Info? (y/n) [Default: n]: ";
      $activate_order_info=$this->read_stdin();

      if($activate_order_info=="y"):
        echo "productIds: ";
        $product_ids=$this->read_stdin();
        echo "Quantity: ";
        $order_quantity=$this->read_stdin();
        echo "Type of Commodity: ";
        $order_commodity=$this->read_stdin();
        echo "Process Order Date: ";
        $order_date=$this->read_stdin();
        echo "Insured Currency: ";
        $insured_currency=$this->read_stdin();
        echo "Insured Value: ";
        $insured_value=$this->read_stdin();
        echo "Global Trade Item Number: ";
        $gtin=$this->read_stdin();
      endif;

      $request=array(
        "eOrderId"=>$eOrderId,
        "sender"=>array(
          "contactName"=>$sender_name,
          "contactNumber"=>$sender_number,
          "contactEmail"=>$sender_email,
          "location"=>array(
            "address"=>$sender_addrress,
            "address2"=>$sender_addrress2,
            "city"=>$sender_city,
            "countryCode"=>$sender_country,
            "postalCode"=>$sender_postcode
          ),
        ),
        "receiver"=>array(
          "contactName"=>$receiver_name,
          "contactNumber"=>$receiver_number,
          "companyName"=>$receiver_company,
          "contactEmail"=>$receiver_email,
          "location"=>array(
            "address"=>$receiver_addrress,
            "address2"=>$receiver_addrress2,
            "city"=>$receiver_city,
            "countryCode"=>$receiver_country,
            "postalCode"=>$receiver_postcode
          )
        ),
        "pickupTime"=>$pickup_time,
        "requestDateTime"=>$request_time,
        "parcels"=>array(
          "description"=>$parcel_description,
          "orderRemarks"=>$parcel_remarks,
          "parcelsInfo"=>array(
            "length"=>$parcel_length,
            "height"=>$parcel_height,
            "width"=>$parcel_width,
            "declaredCurrency"=>$parcel_declared_currency,
            "declaredValue"=>$parcel_declared_value
          ),
          "merchantInfo"=>array(
            "eStoreId"=>$estore_id,
            "merchantId"=>$merchant_id
          ),
          "orderInfo"=>array(
            "productIds"=>$product_ids,
            "orderQuantity"=>$order_quantity,
            "commodity"=>$order_commodity,
            "processOrderDate"=>$order_date,
            "insuredCurrency"=>$insured_currency,
            "insuredValue"=>$insured_value,
            "gtin"=>$gtin
          )
        )
      );
    elseif($input==2):
      include("delivery_order.txt");
    else:
      $this->create_delivery_order($token);
    endif;

    $url=$this->config_base_url().$this->config_create_order_endpoint();
    $data_string=json_encode($request);

    $response=$this->send($url,"default","POST",$data_string,$token);

    echo "\n\n";
    echo "Create Delivery Order Request:\n";
    echo "==============================\n\n";
    print_r(json_decode($data_string));
    echo "\n";
    $this->test_TR46_create_delivery_order_request($request);
    echo "Create Delivery Order Response:\n";
    echo "===============================\n\n";
    if ($response=="Internal server error"):
      echo $response;
    else:
      print_r(json_decode($response));
      echo "\n";
      $this->test_TR46_create_delivery_order_response($response);
    endif;

    echo "\n";
    $this->wait_for_input();
  }

  // Get Status
  function get_delivery_status($token) {
    echo "Enter a tracking number: ";
    $input=$this->read_stdin();

    if($input!=""):
      $url=$this->config_base_url().$this->config_get_status_endpoint()."$input";
      $data_string=json_encode($request);
      $response=$this->send($url,"default","GET",$data_string,$token);

      echo "\n\n";
      echo "Get Delivery Status Request:\n";
      echo "==============================\n\n";
      echo "GET $url\n\n";
      echo "\n";
      echo "Get Delivery Status Response:\n";
      echo "===============================\n\n";
      if ($response=="Internal server error"):
        echo $response;
      else:
        print_r(json_decode($response));
        $this->test_TR46_get_delivery_status_response($response);
      endif;

      $this->wait_for_input();
    else:
      $this->get_delivery_status($token);
      $this->wait_for_input();
    endif;
  }

  // Cancel Order
  function cancel_order($token) {
    echo "Enter the deliveryId that you want to cancel: ";
    $deliveryId=$this->read_stdin();

    echo "Enter the reason for cancellation: ";
    $reason=$this->read_stdin();

    $request=array(
      "deliveryId"=>$deliveryId,
      "reason"=>$reason
    );

    if($deliveryId!="" and $request!=""):
      $url=$this->config_base_url().$this->config_cancel_order_endpoint();

      $data_string=json_encode($request);
      $response=$this->send($url,"default","PUT",$data_string,$token);

      echo "\n\n";
      echo "Cancel Delivery Request:\n";
      echo "==============================\n\n";

      print_r($request);
      echo "\n";
      $this->test_TR46_cancel_delivery_request($request);

      echo "\n";
      echo "Cancel Delivery Response:\n";
      echo "===============================\n\n";
      if ($response=="Internal server error"):
        echo $response;
      else:
        print_r(json_decode($response));
        echo "\n";
        $this->test_TR46_cancel_delivery_response($response);
      endif;

      $this->wait_for_input();
    else:
      echo "\nSorry. deliveryId and reason can't be empty.\n\n";
      $this->cancel_order($token);
      $this->wait_for_input();
    endif;
  }

  // test Create Order Request for TR46 compliance
  function test_TR46_create_delivery_order_request($request) {

    $tr46_keys = array("eOrderId","sender","contactName","contactNumber","location","address","countryCode","postalCode","receiver","pickupTime","parcels","description");

    echo "TR46:2016 Compliance Check\n";
    echo "==========================\n";

    foreach($tr46_keys as $key):

      if (in_array($key, $this->array_keys_multi($request))):
        $this->key_exists_grade($key,"pass");
      else:
        $this->key_exists_grade($key,"fail");
      endif;

    endforeach;

    echo "\n\n";
  }

  // test Create Order Response for TR46 compliance
  function test_TR46_create_delivery_order_response($response) {
    $tr46_keys = array("status","responseTime","delivery","eOrderId","deliveryId");

    echo "TR46:2016 Compliance Check\n";
    echo "==========================\n";

    $array_response = (array) $response;

    foreach($tr46_keys as $key):
      if (in_array($key, $this->array_keys_multi($array_response))):
        $this->key_exists_grade($key,"pass");
      else:
        $this->key_exists_grade($key,"fail");
      endif;

    endforeach;

    $iterator = new RecursiveIteratorIterator(new RecursiveArrayIterator(json_decode($response)));

    $status="";

    foreach($iterator as $key=>$value):

      if($key==="status"):
        $status=$value;
      endif;

      if ($key === "status"):

        if(is_string($value) and strlen($value)<="35"):
          $this->key_is_valid_grade($key,"pass");
        else:
          $this->key_is_valid_grade($key,"fail");
        endif;

      endif;

      if ($key === "deliveryId"):

        if((is_string($value) and strlen($value)<="35") or (is_null($value) and $status=="Failed")):
          $this->key_is_valid_grade($key,"pass");
        else:
          $this->key_is_valid_grade($key,"fail");
        endif;

      endif;

      if($key === "responseTime"):

        $date=str_replace("T"," ",$value);
        $date = substr($date, 0, strpos($date, "."));

        if($this->is_date($date)):
          $this->key_is_valid_grade($key,"pass");
        else:
          $this->key_is_valid_grade($key,"fail");
        endif;

      endif;

      if ($key === "eOrderId"):

        if((is_string($value) and strlen($value)<="64") or (is_null($value) and $status=="Failed")):
          $this->key_is_valid_grade($key,"pass");
        else:
          $this->key_is_valid_grade($key,"fail");
        endif;

      endif;

    endforeach;

    echo "\n\n";
  }

  // test Get Delivery Status Response for TR46 compliance
  function test_TR46_get_delivery_status_response($response) {
    $tr46_keys = array("deliveryId");

    echo "TR46:2016 Compliance Check\n";
    echo "==========================\n";

    $array_response = (array) $response;

    foreach($tr46_keys as $key):

      if (in_array($key, $this->array_keys_multi($array_response))):
        $this->key_exists_grade($key,"pass");
      else:
        $this->key_exists_grade($key,"fail");
      endif;

    endforeach;

    $iterator = new RecursiveIteratorIterator(new RecursiveArrayIterator(json_decode($response)));

    foreach($iterator as $key=>$value):
      if ($key === "deliveryId"):

        if(is_string($value) and strlen($value)<="35"):
          $this->key_is_valid_grade($key,"pass");
        else:
          $this->key_is_valid_grade($key,"fail");
        endif;

      endif;

    endforeach;

    echo "\n\n";
  }

  // test Cancel Delivery Request for TR46 compliance
  function test_TR46_cancel_delivery_request($request) {
    $tr46_keys = array("deliveryId","reason");
    $array_request = (Array) $request;

    echo "TR46:2016 Compliance Check\n";
    echo "==========================\n";

    foreach($tr46_keys as $key):

      if (in_array($key, $this->array_keys_multi($array_request))):
        $this->key_exists_grade($key,"pass");
      else:
        $this->key_exists_grade($key,"fail");
      endif;

    endforeach;

    echo "\n\n";
  }

  // test Cancel Delivery Response for TR46 compliance
  function test_TR46_cancel_delivery_response($response) {
    $tr46_keys = array("deliveryId","reason");
    $array_response = (Array) $response;

    echo "TR46:2016 Compliance Check\n";
    echo "==========================\n";

    foreach($tr46_keys as $key):

      if (in_array($key, $this->array_keys_multi($array_response))):
        $this->key_exists_grade($key,"pass");
      else:
        $this->key_exists_grade($key,"fail");
      endif;

    endforeach;

    $iterator = new RecursiveIteratorIterator(new RecursiveArrayIterator(json_decode($response)));

    foreach($iterator as $key=>$value):
      if ($key === "deliveryId" or $key === "status"):

        if(is_string($value) and strlen($value)<="35"):
          $this->key_is_valid_grade($key,"pass");
        else:
          $this->key_is_valid_grade($key,"fail");
        endif;

      endif;

    endforeach;

    echo "\n\n";
  }

  // test if key exists
  function key_exists_grade($key,$grade) {
    if($grade=="pass"):
      echo "Testing $key exists: \033[32m Pass!\033[0m\n";
    elseif($grade=="fail"):
      echo "Testing $key exists: \033[31m Fail!\033[0m\n";
    endif;
  }

  // test if key is valid
  function key_is_valid_grade($key,$grade) {
    if($grade=="pass"):
      echo "Testing $key is valid: \033[32m Pass!\033[0m\n";
    elseif($grade=="fail"):
      echo "Testing $key is valid: \033[31m Fail!\033[0m\n";
    endif;
  }

  // Wait for input before proceed next action
  function wait_for_input() {
    echo "\nPress enter to do another test or type `quit` to exit...: ";
    $input=$this->read_stdin();

    if($input!="quit"):
      $this->start();
    else:
      $ascii_art="
      _______   ______     ______    _______  .______   ____    ____  _______
     /  _____| /  __  \   /  __  \  |       \ |   _  \  \   \  /   / |   ____|
    |  |  __  |  |  |  | |  |  |  | |  .--.  ||  |_)  |  \   \/   /  |  |__
    |  | |_ | |  |  |  | |  |  |  | |  |  |  ||   _  <    \_    _/   |   __|
    |  |__| | |  `--'  | |  `--'  | |  '--'  ||  |_)  |     |  |     |  |____
     \______|  \______/   \______/  |_______/ |______/      |__|     |_______|

  ";

      echo $ascii_art;
      echo "    Copyright (c) Hazrul Azhar Jamari. Website: https://www.hazrulazhar.com. Github: https://github.com/abanghazrul\n\n";
      exit;
    endif;
  }


}

?>
