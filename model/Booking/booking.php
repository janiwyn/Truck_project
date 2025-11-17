<?php
namespace model\Booking;

use model\App;
use sys\store\AppData;

class booking extends App
{

    private $TableName = "tbl_booking";
    private bool $UserIsActivated = false;

    public function __construct()
    {
        parent::__construct();
        $this->__initialize();
    }
  
 public function __create_booking($user_id,$driver_id, $pickup_text, $dropoff_text, $truck_type){
          // Convert location text to lat/lng
        $pickup = $this->__geocode_location($pickup_text);
        $dropoff = $this->__geocode_location($dropoff_text);
  var_dump($pickup);
        var_dump($dropoff);
        exit;
        if (!$pickup || !$dropoff) {
            $this->Error = "Invalid pickup or drop-off location.";
            return false;
        }
      

        // Get real distance
        $distance_km = $this->__get_real_distance($pickup, $dropoff);
        if ($distance_km <= 0) {
            $this->Error = "Failed to calculate distance.";
            return false;
        }

        // Price calculation
        $price_data = $this->__calculate_price($truck_type, $distance_km);
        $base_price = $price_data["price"];

        // Check for night charge
        $hour = date('H');
        $isNight = ($hour >= 20 || $hour < 6);
        $night_charge = $isNight ? ($base_price * 0.05) : 0;
        $final_price = $base_price + $night_charge;

        // Save booking
        $data = [
            'user_id' => $user_id,
            'driver_id' => $driver_id,
            'pickup_location' => $pickup_text,
            'dropoff_location' => $dropoff_text,
            'truck_type' => $truck_type,
            'distance_km' => $distance_km,
            'price' => $final_price,
            'isNight' => $isNight ? 1 : 0,
            'status' => 'pending',
            'payment_status' => 'unpaid'
        ];

        if ($result = AppData::__create($this->TableName, $data)) {

            // Update truck status
            $update_sql = "UPDATE tbl_truck SET status = 'on_trip' WHERE driver_id = '$driver_id'";
            AppData::__execute($update_sql);

            return [
                "booking_id" => $result,
                "distance" => $distance_km,
                "price" => $final_price,
                "night_charge" => $night_charge,
                "isNight" => $isNight
            ];
        }

        $this->Error = "Failed to create booking";
        return false;


 }

 public function __list_bookings(){
    $sql = "SELECT * FROM `$this->TableName` where id > 0";
    $res = AppData::__execute($sql);
    if($res->num_rows == 0) : 
        $this->Error = "No booking found";
        return false;
    endif;
    $list = [];
    while($row = $res->fetch_assoc()) : 
        $list[] = $row;
    endwhile;

    return $list;
 }
 // convert name -> lat/lng
    private function __geocode_location($location)
    {
        $apiKey = getenv("GOOGLE-API-KEY");
      //  var_dump($apiKey);
      //  exit;

        $location = urlencode($location);

        $url = "https://maps.googleapis.com/maps/api/geocode/json?address=$location&key=$apiKey";
        $response = file_get_contents($url);
                echo "<pre>";
print_r($response);
echo "</pre>";
        $data = json_decode($response, true);


        if ($data['status'] != "OK") return false;

        return [
            "lat" => $data['results'][0]['geometry']['location']['lat'],
            "lng" => $data['results'][0]['geometry']['location']['lng']
        ];
    }

 private function __get_real_distance($pickup, $dropoff){
    $apiKey = getenv("GOOGLE-API-KEY");

        $url = "https://maps.googleapis.com/maps/api/distancematrix/json?units=metric"
            . "&origins={$pickup['lat']},{$pickup['lng']}"
            . "&destinations={$dropoff['lat']},{$dropoff['lng']}"
            . "&key=$apiKey";

        $res = json_decode(file_get_contents($url), true);

        if ($res['status'] != "OK") return 0;
        return $res['rows'][0]['elements'][0]['distance']['value'] / 1000;


 }
 public function __calculate_price($truck_type, $distance_km){
   $truck_prices = [
            "Isuzu" => 200000,
            "Fuso" => 400000,
            "Toyota Dyna" => 250000,
            "Mitsubishi Canter" => 300000,
            "Mazda Titan" => 150000,
            "Tricycle" => 100000
        ];

        $price_per_km = $truck_prices[$truck_type] ?? 200000;
        $price = $distance_km * $price_per_km;

        return [
            "distance_km" => $distance_km,
            "price" => $price
        ];
 }

 public function __select_truck($truck_id){
    $sql = "SELECT * FROM `tbl_truck` WHERE id = '$truck_id' AND status = 'available'";
    $res = AppData::__execute($sql);

    if($res->num_rows == 0) : 
        $this->Error = "Truck not available";
        return false;
    endif;
    return $res->fetch_assoc();
 }

 // history for the user 
 public function __user_history($user_id)
{
       $sql = "
        SELECT 
            b.id AS booking_id,
            b.pickup_location,
            b.dropoff_location,
            b.truck_type,
            b.distance_km,
            b.price,
            b.isNight,
            b.status,
            b.created_at,
            u.first_name AS driver_name,
            t.truck_number_plate AS truck_plate,
            t.truck_name,
            t.truck_type,
            t.capacity_tons,
            t.license_image,
            t.permit_image
        FROM tbl_booking b
        LEFT JOIN tbl_user u ON b.driver_id = u.id
        LEFT JOIN tbl_trucks t ON t.driver_id = b.driver_id
        WHERE b.user_id = '$user_id'
        ORDER BY b.id DESC
    ";

    $res = AppData::__execute($sql);

    if ($res->num_rows == 0) {
        $this->Error = "No booking history found.";
        return false;
    }

    $history = [];
    while ($row = $res->fetch_assoc()) {
        $history[] = $row;
    }

    return $history;
}
// searching function
public function __search_place($query)
{
    $apiKey = getenv("GOOGLE-API-KEY");
    $query = urlencode($query);

    $url = "https://maps.googleapis.com/maps/api/place/textsearch/json?query={$query}&key={$apiKey}";

    $response = file_get_contents($url);
    if (!$response) {
        $this->Error = "Failed to connect to Google API.";
        return false;
    }

    $data = json_decode($response, true);

    if ($data['status'] != 'OK') {
        $this->Error = $data['status'] ?? "No results found.";
        return false;
    }

    // Prepare results
    $places = [];
    foreach ($data['results'] as $place) {
        $places[] = [
            'name' => $place['name'],
            'address' => $place['formatted_address'],
            'lat' => $place['geometry']['location']['lat'],
            'lng' => $place['geometry']['location']['lng'],
            'place_id' => $place['place_id']
        ];
    }

    return $places;
}




    private function __initialize()
    {
        if (!AppData::__table_exists($this->TableName)) {
            $query = "CREATE TABLE `$this->TableName` (";
            $query .= "`id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,";
            $query .= " `user_id` INT(11) NOT NULL,";
            $query .= "`driver_id` INT(11) NULL DEFAULT NULL,";
            $query .= "`pickup_location` VARCHAR(255) NOT NULL,";
            $query .= "`dropoff_location` VARCHAR(255) NOT NULL,";
            $query .= " `truck_type` VARCHAR(50) NOT NULL,";
            $query .= " `distance_km` FLOAT NULL,";
            $query .= " `price` DECIMAL(10,2) NULL,";
            $query .= " `current_lat` DOUBLE NULL,";
            $query .= " `current_lng` DOUBLE NULL,";
            $query .= " `isNight` TINYINT(1) DEFAULT 0,";
            $query .= " `status` ENUM('pending','accepted','on_trip','completed','cancelled') DEFAULT 'pending',";
            $query .= " `payment_status` ENUM('unpaid','paid') DEFAULT 'unpaid',";
            $query .= " `created_at` timestamp NOT NULL DEFAULT current_timestamp(),";
            $query .= " `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),";
            $query .= "CONSTRAINT FOREIGN KEY (`user_id`) REFERENCES `tbl_user`(`id`),";
            $query .= "CONSTRAINT FOREIGN KEY (`driver_id`) REFERENCES `tbl_user`(`id`)";
            $query .= ") ENGINE=InnoDB";
            AppData::__execute($query);


            //create default user
            $this->__create_booking("1", "1", "kampala", "kitala", "Isuzu");
        }
    }



}

?>