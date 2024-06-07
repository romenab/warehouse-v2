<?php

require_once 'vendor/autoload.php';

use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\ConsoleOutput;
use Carbon\Carbon;

class Warehouse
{
    private Tasks $allProducts;

    public function __construct(Tasks $allProducts)
    {
        $this->allProducts = $allProducts;
    }

    public function getTable(): void
    {
        $output = new ConsoleOutput();
        $table = new Table($output);

        $table->setHeaders(['ID', 'Name', 'Price €', 'Created', 'Last updated', 'Units', 'Expiration date']);

        foreach ($this->allProducts->getAllProducts() as $product) {
            $createdAt = Carbon::parse($product['createdAt'])->setTimezone('Europe/Riga');
            $updatedAt = Carbon::parse($product['updatedAt'])->setTimezone('Europe/Riga');
            if($product['expiredAt'] !== null){
                $expiredAt = Carbon::parse($product['expiredAt'])->setTimezone('Europe/Riga')->toDateString();
            }else{
                $expiredAt = null;
            }
            $price = number_format($product['price'] / 100, 2);
            $table->addRow([
                $product['id'],
                $product['name'],
                $price,
                $createdAt,
                $updatedAt,
                $product['units'],
                $expiredAt
            ]);
        }
        $table->render();
    }

    public function getChanges(): void
    {
        $output = new ConsoleOutput();
        $table = new Table($output);

        $table->setHeaders(['User', "Action", 'ID', 'Name', 'Units', 'Last updated']);

        foreach ($this->allProducts->getChangesMade() as $changed) {
            $updatedAt = Carbon::parse($changed['updatedAt'])->setTimezone('Europe/Riga')->toDateTimeString();
            $table->addRow([
                $changed['username'],
                $changed['action'],
                $changed['id'],
                $changed['name'],
                $changed['units'],
                $updatedAt
            ]);
        }
        $table->render();
    }

    public function chooseAction(string $userAction): void
    {
        switch ($userAction) {
            case 1:
                $this->allProducts->add();
                break;
            case 2:
                $this->allProducts->update();
                break;
            case 3:
                $this->allProducts->delete();
                break;
            case 4:
                $this->getChanges();
                $userChoice = readline("Make more changes (1) or exit (2): ");
                if ($userChoice == 2) {
                    $this->allProducts->save("products.json", "logChanges.json");
                    exit("Goodbye!");
                }
                break;
            case 5:
                $this->allProducts->report();
                break;
            case 6:
                $this->allProducts->save("products.json", "logChanges.json");
                exit("Goodbye!");
            default:
                echo "Invalid input!" . PHP_EOL;
        }
        $this->getTable();
    }
    public function getMenu(): void
    {
        echo "1. Add new product" . PHP_EOL .
            "2. Update product" . PHP_EOL .
            "3. Delete product" . PHP_EOL .
            "4. View changes log" . PHP_EOL .
            "5. View report" . PHP_EOL .
            "6. Exit" . PHP_EOL;
    }

}