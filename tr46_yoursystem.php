<?php

class TR46_YourSystem extends TR46_Base {
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
}

?>
