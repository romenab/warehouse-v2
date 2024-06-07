<?php
require_once "app/Tasks.php";
require_once "app/Warehouse.php";
$user = new User();
$user->logIn();
$allProducts = new Tasks($user);
$allProducts->load("products.json", "logChanges.json");
$show = new Warehouse($allProducts);
echo "Welcome to Warehouse!" . PHP_EOL;
$show->getTable();
while (true) {
    $show->getMenu();
    $userAction = (int)readline("Enter your action: ");
    $show->chooseAction($userAction);
}
