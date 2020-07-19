<?php

use Surcouf\PhpArchive\Database\EAggregationType;
use Surcouf\PhpArchive\Database\EQueryType;
use Surcouf\PhpArchive\Database\QueryBuilder;
use Surcouf\PhpArchive\Address;
use Surcouf\PhpArchive\Customer;
use Surcouf\PhpArchive\Salutation;

$Controller->post(array(
  'pattern' => '/ajax/admin/search/address',
  'requiredPayload' => array('keyword'),
  'fn' => 'ui_ajax_admin_search_address'
));

$Controller->get(array(
  'pattern' => '/ajax/admin/search/customerWithAddress/(?<id>\d+)',
  'fn' => 'ui_ajax_admin_search_customer4address'
));

function ui_ajax_admin_search_address() {
  global $Controller;

  $data = $Controller->Dispatcher()->getPayload();
  if ($data['keyword'] == '')
    return $Controller->Config()->getResponseArray(3);

  $query = new QueryBuilder(EQueryType::qtSELECT, 'addresses', DB_ANY);
  $query->where('addresses', 'address_title', 'LIKE', '%'.$data['keyword'].'%')
        ->orWhere('addresses', 'address_line1', 'LIKE', '%'.$data['keyword'].'%')
        ->orWhere('addresses', 'address_line2', 'LIKE', '%'.$data['keyword'].'%')
        ->orWhere('addresses', 'address_line3', 'LIKE', '%'.$data['keyword'].'%')
        ->orWhere('addresses', 'address_line4', 'LIKE', '%'.$data['keyword'].'%')
        ->orWhere('addresses', 'address_zip', 'LIKE', '%'.$data['keyword'].'%')
        ->orWhere('addresses', 'address_city', 'LIKE', '%'.$data['keyword'].'%')
        ->orderBy('address_title');
  $result = $Controller->select($query);
  if ($result->num_rows == 0)
    return $Controller->Config()->getResponseArray(3);
  $records = array();
  while ($record = $result->fetch_assoc()) {
    $entry = new Address($record);
    $records[$entry->getId()] = array(
      'name' => $entry->getName(),
      'zip' => $entry->getZip(),
      'city' => $entry->getCity(),
      'country' => $entry->getCountry()->getCode(),
    );
    if (count($records) == $Controller->Config()->lsModalEntriesCount->getInt())
      break;
  }
  $response = $Controller->Config()->getResponseArray(1);
  $response['Records'] = $records;
  $response['Count'] = $result->num_rows;
  $response['Limit'] = $Controller->Config()->lsModalEntriesCount->getInt();
  return $response;

} // ui_ajax_admin_search_address()

function ui_ajax_admin_search_customer4address() {
  global $Controller;

  $id = $Controller->Dispatcher()->getMatchInt('id');
  if ($id <= 0)
    return $Controller->Config()->getResponseArray(80);

  $adr = $Controller->getAddress($id);
  if (is_null($adr))
    return $Controller->Config()->getResponseArray(80);

  $count = $Controller->selectCountSimple('customers', 'address_id_main', $id)
    + $Controller->selectCountSimple('customers', 'address_id_invoice', $id)
    + $Controller->selectCountSimple('customers', 'address_id_admin', $id);

  $response = $Controller->Config()->getResponseArray(1);
  $response['InUse'] = ($count > 0);

  return $response;
} // ui_ajax_admin_search_customer4address()
