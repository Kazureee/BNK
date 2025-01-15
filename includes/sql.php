<?php
  require_once('includes/load.php');

/*--------------------------------------------------------------*/
/* Function for find all database table rows by table name
/*--------------------------------------------------------------*/
function find_all($table) {
   global $db;
   if(tableExists($table))
   {
     return find_by_sql("SELECT * FROM ".$db->escape($table));
   }
}
/*--------------------------------------------------------------*/
/* Function for Perform queries
/*--------------------------------------------------------------*/
function find_by_sql($sql)
{
  global $db;
  $result = $db->query($sql);
  $result_set = $db->while_loop($result);
 return $result_set;
}
/*--------------------------------------------------------------*/
/*  Function for Find data from table by id
/*--------------------------------------------------------------*/
function find_by_id($table,$id)
{
  global $db;
  $id = (int)$id;
    if(tableExists($table)){
          $sql = $db->query("SELECT * FROM {$db->escape($table)} WHERE id='{$db->escape($id)}' LIMIT 1");
          if($result = $db->fetch_assoc($sql))
            return $result;
          else
            return null;
     }
}
/*--------------------------------------------------------------*/
/* Function for Delete data from table by id
/*--------------------------------------------------------------*/
function delete_by_id($table,$id)
{
  global $db;
  if(tableExists($table))
   {
    $sql = "DELETE FROM ".$db->escape($table);
    $sql .= " WHERE id=". $db->escape($id);
    $sql .= " LIMIT 1";
    $db->query($sql);
    return ($db->affected_rows() === 1) ? true : false;
   }
}
/*--------------------------------------------------------------*/
/* Function for Count id  By table name
/*--------------------------------------------------------------*/

function count_by_id($table){
  global $db;
  if(tableExists($table))
  {
    $sql    = "SELECT COUNT(id) AS total FROM ".$db->escape($table);
    $result = $db->query($sql);
     return($db->fetch_assoc($result));
  }
}
/*--------------------------------------------------------------*/
/* Determine if database table exists
/*--------------------------------------------------------------*/
function tableExists($table){
  global $db;
  $table_exit = $db->query('SHOW TABLES FROM '.DB_NAME.' LIKE "'.$db->escape($table).'"');
      if($table_exit) {
        if($db->num_rows($table_exit) > 0)
              return true;
         else
              return false;
      }
  }
/*--------------------------------------------------------------*/
/* Login with the data provided in $_POST,
 /* coming from the login form.
/*--------------------------------------------------------------*/
  function authenticate($username='', $password='') {
    global $db;
    $username = $db->escape($username);
    $password = $db->escape($password);
    $sql  = sprintf("SELECT id,username,password,user_level FROM users WHERE username ='%s' LIMIT 1", $username);
    $result = $db->query($sql);
    if($db->num_rows($result)){
      $user = $db->fetch_assoc($result);
      $password_request = sha1($password);
      if($password_request === $user['password'] ){
        return $user['id'];
      }
    }
   return false;
}

  /*--------------------------------------------------------------*/
  /* Login with the data provided in $_POST,
  /* coming from the login_v2.php form.
  /* If you used this method then remove authenticate function.
 /*--------------------------------------------------------------*/
 
 /*--------------------------------------------------------------*/
 /* Authenticate function version 2
 /*--------------------------------------------------------------*/
 function authenticate_v2($username='', $password='') {
    global $db;
    $username = $db->escape($username);
    $password = $db->escape($password);
    $sql  = sprintf("SELECT id,username,password,user_level FROM users WHERE username ='%s' LIMIT 1", $username);
    $result = $db->query($sql);
    if($db->num_rows($result)){
      $user = $db->fetch_assoc($result);
      $password_request = sha1($password);
      if($password_request === $user['password'] ){
        return $user;
      }
    }
   return false;
 }

 /*--------------------------------------------------------------*/
 /* Find current logged in user by session id
 /*--------------------------------------------------------------*/
 function current_user(){
     static $current_user;
     global $db;
     if(!$current_user){
        if(isset($_SESSION['user_id'])):
            $user_id = intval($_SESSION['user_id']);
            $current_user = find_by_id('users',$user_id);
       endif;
     }
   return $current_user;
 }

 /*--------------------------------------------------------------*/
 /* Find all users by joining users table and user groups table
 /*--------------------------------------------------------------*/
 function find_all_user(){
     global $db;
     $results = array();
     $sql = "SELECT u.id,u.name,u.username,u.user_level,u.status,u.last_login,";
     $sql .="g.group_name ";
     $sql .="FROM users u ";
     $sql .="LEFT JOIN user_groups g ";
     $sql .="ON g.group_level=u.user_level ORDER BY u.name ASC";
     $result = find_by_sql($sql);
     return $result;
 }

 /*--------------------------------------------------------------*/
 /* Function to update the last login of a user
 /*--------------------------------------------------------------*/
 function updateLastLogIn($user_id)
 {
   global $db;
   $date = make_date();
   $sql = "UPDATE users SET last_login='{$date}' WHERE id ='{$user_id}' LIMIT 1";
   $result = $db->query($sql);
   return ($result && $db->affected_rows() === 1 ? true : false);
 }

 /*--------------------------------------------------------------*/
 /* Find group by group name
 /*--------------------------------------------------------------*/
 function find_by_groupName($val)
 {
   global $db;
   $sql = "SELECT group_name FROM user_groups WHERE group_name = '{$db->escape($val)}' LIMIT 1 ";
   $result = $db->query($sql);
   return($db->num_rows($result) === 0 ? true : false);
 }

 /*--------------------------------------------------------------*/
 /* Find group level by group level value
 /*--------------------------------------------------------------*/
 function find_by_groupLevel($level)
 {
   global $db;
   $sql = "SELECT group_level FROM user_groups WHERE group_level = '{$db->escape($level)}' LIMIT 1 ";
   $result = $db->query($sql);
   return($db->num_rows($result) === 0 ? true : false);
 }

 /*--------------------------------------------------------------*/
 /* Function for checking user level access to page
 /*--------------------------------------------------------------*/
 function page_require_level($require_level){
   global $session;
   $current_user = current_user();
   $login_level = find_by_groupLevel($current_user['user_level']);
   // If user not logged in
   if (!$session->isUserLoggedIn(true)):
     $session->msg('d','Please login...');
     redirect('index.php', false);
   // If Group status is inactive
   elseif($login_level['group_status'] === '0'):
     $session->msg('d','This level user has been banned!');
     redirect('home.php',false);
   // Checking if logged-in user level is less than or equal to required level
   elseif($current_user['user_level'] <= (int)$require_level):
     return true;
   else:
     $session->msg("d", "Sorry! you don't have permission to view the page.");
     redirect('home.php', false);
   endif;
 }

   /*--------------------------------------------------------------*/
   /* Function for Finding all product name
   /* JOIN with categorie  and media database table
   /*--------------------------------------------------------------*/
   
   /*--------------------------------------------------------------*/
   /* Join product table to retrieve product information
   /*--------------------------------------------------------------*/
   
   /*--------------------------------------------------------------*/
   /* Join product table to retrieve product information
   /*--------------------------------------------------------------*/
   function join_product_table(){
      global $db;
      $sql  = "SELECT p.id, p.name, p.quantity, p.buy_price, p.sale_price, p.date, c.name AS categorie ";
      $sql .= "FROM products p ";
      $sql .= "LEFT JOIN categories c ON c.id = p.categorie_id ";
      $sql .= "ORDER BY p.id ASC";
      return find_by_sql($sql);
   }
 
   /*--------------------------------------------------------------*/
   /* Function for Finding all product names
   /* Request coming from ajax.php for auto-suggest
   /*--------------------------------------------------------------*/
   function find_product_by_title($product_name){
      global $db;
      $p_name = remove_junk($db->escape($product_name));
      $sql = "SELECT name FROM products WHERE name LIKE '%$p_name%' LIMIT 5";
      $result = find_by_sql($sql);
      return $result;
   }
 
   /*--------------------------------------------------------------*/
   /* Function for Finding all product info by product title
   /* Request coming from ajax.php
   /*--------------------------------------------------------------*/
   function find_all_product_info_by_title($title){
     global $db;
     $sql  = "SELECT * FROM products ";
     $sql .= "WHERE name ='{$title}' ";
     $sql .= "LIMIT 1";
     return find_by_sql($sql);
   }
 
   /*--------------------------------------------------------------*/
   /* Function for updating product quantity
   /*--------------------------------------------------------------*/
   function update_product_qty($qty, $p_id){
     global $db;
     $qty = (int) $qty;
     $id  = (int) $p_id;
     $sql = "UPDATE products SET quantity = quantity - '{$qty}' WHERE id = '{$id}'";
     $result = $db->query($sql);
     return ($db->affected_rows() === 1 ? true : false);
   }
 
   /*--------------------------------------------------------------*/
   /* Function for displaying recently added products
   /*--------------------------------------------------------------*/
   function find_recent_product_added($limit){
     global $db;
     $sql = "SELECT p.id, p.name, p.sale_price, c.name AS categorie ";
     $sql .= "FROM products p ";
     $sql .= "LEFT JOIN categories c ON c.id = p.categorie_id ";
     $sql .= "ORDER BY p.id DESC LIMIT " . $db->escape((int)$limit);
     return find_by_sql($sql);
   }

 
 /*--------------------------------------------------------------*/
 /* Function for Find Highest saleing Product
 /*--------------------------------------------------------------*/
 /*--------------------------------------------------------------*/
 /* Function to find the highest selling products
 /*--------------------------------------------------------------*/
 function find_higest_saleing_product($limit){
   global $db;
   $sql  = "SELECT p.name, COUNT(s.product_id) AS totalSold, SUM(s.qty) AS totalQty";
   $sql .= " FROM sales s";
   $sql .= " LEFT JOIN products p ON p.id = s.product_id ";
   $sql .= " GROUP BY s.product_id";
   $sql .= " ORDER BY SUM(s.qty) DESC LIMIT ".$db->escape((int)$limit);
   return $db->query($sql);
 }

 /*--------------------------------------------------------------*/
 /* Function for finding all sales
 /*--------------------------------------------------------------*/
 function find_all_sale(){
   global $db;
   $sql  = "SELECT s.id, s.qty, s.price, s.date, p.name";
   $sql .= " FROM sales s";
   $sql .= " LEFT JOIN products p ON s.product_id = p.id";
   $sql .= " ORDER BY s.date DESC";
   return find_by_sql($sql);
 }

 /*--------------------------------------------------------------*/
 /* Function for displaying recent sales
 /*--------------------------------------------------------------*/
 function find_recent_sale_added($limit){
   global $db;
   $sql  = "SELECT s.id, s.qty, s.price, s.date, p.name";
   $sql .= " FROM sales s";
   $sql .= " LEFT JOIN products p ON s.product_id = p.id";
   $sql .= " ORDER BY s.date DESC LIMIT ".$db->escape((int)$limit);
   return find_by_sql($sql);
 }

 /*--------------------------------------------------------------*/
 /* Function to generate sales report by two dates
 /*--------------------------------------------------------------*/
 function find_sale_by_dates($start_date, $end_date){
   global $db;
   $start_date  = date("Y-m-d", strtotime($start_date));
   $end_date    = date("Y-m-d", strtotime($end_date));
   $sql  = "SELECT s.date, p.name, p.sale_price, p.buy_price,";
   $sql .= " COUNT(s.product_id) AS total_records,";
   $sql .= " SUM(s.qty) AS total_sales,";
   $sql .= " SUM(p.sale_price * s.qty) AS total_saleing_price,";
   $sql .= " SUM(p.buy_price * s.qty) AS total_buying_price ";
   $sql .= " FROM sales s ";
   $sql .= " LEFT JOIN products p ON s.product_id = p.id";
   $sql .= " WHERE s.date BETWEEN '{$start_date}' AND '{$end_date}'";
   $sql .= " GROUP BY DATE(s.date), p.name";
   $sql .= " ORDER BY DATE(s.date) DESC";
   return $db->query($sql);
 }

 /*--------------------------------------------------------------*/
 /* Function to generate daily sales report
 /*--------------------------------------------------------------*/
 function dailySales($year, $month){
   global $db;
   $sql  = "SELECT s.qty,";
   $sql .= " DATE_FORMAT(s.date, '%Y-%m-%e') AS date, p.name,";
   $sql .= " SUM(p.sale_price * s.qty) AS total_saleing_price";
   $sql .= " FROM sales s";
   $sql .= " LEFT JOIN products p ON s.product_id = p.id";
   $sql .= " WHERE DATE_FORMAT(s.date, '%Y-%m') = '{$year}-{$month}'";
   $sql .= " GROUP BY DATE_FORMAT(s.date, '%e'), s.product_id";
   return find_by_sql($sql);
 }

 /*--------------------------------------------------------------*/
 /* Function to generate monthly sales report
 /*--------------------------------------------------------------*/
 function monthlySales($year){
   global $db;
   $sql  = "SELECT s.qty,";
   $sql .= " DATE_FORMAT(s.date, '%Y-%m-%e') AS date, p.name,";
   $sql .= " SUM(p.sale_price * s.qty) AS total_saleing_price";
   $sql .= " FROM sales s";
   $sql .= " LEFT JOIN products p ON s.product_id = p.id";
   $sql .= " WHERE DATE_FORMAT(s.date, '%Y') = '{$year}'";
   $sql .= " GROUP BY DATE_FORMAT(s.date, '%c'), s.product_id";
   $sql .= " ORDER BY DATE_FORMAT(s.date, '%c') ASC";
   return find_by_sql($sql);
 }
?>
