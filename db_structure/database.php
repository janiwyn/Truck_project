<?php

// tbl_role
if (!table_exists("tbl_role")) {
    $query = "CREATE TABLE `tbl_role` (";
    $query .= "`id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,";
    $query .= "`role_name` VARCHAR(255) NOT NULL DEFAULT 'NULL',";
    $query .= "`created_at` timestamp NOT NULL DEFAULT current_timestamp(),";
    $query .= "`updated_at` timestamp NOT NULL DEFAULT current_timestamp()";
    $query .= ") ENGINE=InnoDB";
    $result = mysqli_work($query);

    $query = "INSERT INTO `tbl_role`(`role_name`) VALUES";
    $query .= "('Super-Admin'), ('Admin'), ('User')";
    $result = mysqli_work($query);
}



// tbl_user
if (!table_exists("tbl_user")) {
    $query = "CREATE TABLE `tbl_user`(";
    $query .= "`id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,";
    $query .= "`first_name` VARCHAR(255) NOT NULL DEFAULT 'NULL',";
    $query .= "`last_name` VARCHAR(255) NOT NULL DEFAULT 'NULL',";
    $query .= "`username` VARCHAR(255) NOT NULL DEFAULT 'NULL',";
    $query .= "`password` VARCHAR(255) NOT NULL,";
    $query .= "`phone_number` VARCHAR(15) NOT NULL,";
    $query .= "`email` VARCHAR(50) NOT NULL DEFAULT 'NULL',";
    $query .= "`role_id` INT(11) NOT NULL ,";
    $query .= "`created_at` timestamp NOT NULL DEFAULT current_timestamp(),";
    $query .= "`updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),";
    $query .= " CONSTRAINT `FK_user_has_a_role` FOREIGN KEY (`role_id`)";
    $query .= "REFERENCES `tbl_role`(`id`)";
    $query .= ") ENGINE=InnoDB";
    $result = mysqli_work($query);

}

?>