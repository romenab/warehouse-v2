<?php
require_once 'vendor/autoload.php';
require_once 'User.php';
use Carbon\Carbon;
use Ramsey\Uuid\Uuid;


class Tasks
{
    private array $allProducts;
    private User $user;
    private array $changesMade;
    public ?Carbon $createdAt;
    public ?Carbon $updatedAt;



    public function __construct(
        User $user,
        array $allProducts = [],
        array $changesMade = [],
        ?Carbon $createdAt = null,
        ?Carbon $updatedAt = null

    )
    {
        $this->user = $user;
        $this->allProducts = $allProducts;
        $this->changesMade = $changesMade;
        $this->createdAt = $createdAt ? Carbon::parse($createdAt) : Carbon::now();
        $this->updatedAt = $updatedAt ? Carbon::parse($updatedAt) : Carbon::now();
    }



    public function add(): void
    {
        while (true) {
            $userProduct = readline("Enter your product name: ");
            if ($userProduct == "") {
                echo "Invalid input!" . PHP_EOL;
                continue;
            }
            $userUnits = (int)readline("Enter your unit quantity: ");
            if ($userUnits < 0) {
                echo "Invalid input!" . PHP_EOL;
                continue;
            }
            $userPrice = (float)readline("Enter your product price(€): ");
            if($userPrice < 0) {
                echo "Invalid input!" . PHP_EOL;
                continue;
            }
            $cents = $userPrice * 100;
            $userExpiration = readline("Enter your expiration date y-m-d: ");
            try {
                $expirationDate = Carbon::createFromFormat('Y-m-d', $userExpiration);
                if ($expirationDate->isPast()) {
                    echo "Expiration date cannot be in the past!" . PHP_EOL;
                    continue;
                }
                break;
            }catch (\Exception $e){
                $expirationDate = null;
            }

            $uuid = Uuid::uuid4()->toString();
            $this->allProducts[] = [
                "id" => $uuid,
                "name" => $userProduct,
                "price" => $cents,
                "createdAt" => Carbon::now()->toDateTimeString(),
                "updatedAt" => Carbon::now()->toDateTimeString(),
                "units" => $userUnits,
                "expiredAt" => $expirationDate
            ];
            echo "Product added successfully!" . PHP_EOL;
            $this->changesLog("Added", $uuid, $userProduct, $userUnits);
            break;
        }
    }

    public function update(): void
    {
        while (true) {
            $userId = (int)readline("Enter product ID: ");
            $userUpdate = readline("Enter your unit quantity: ");
            if (!is_numeric($userUpdate) && $userUpdate >= 0) {
                echo "Invalid input! Please enter a valid number." . PHP_EOL;
                continue;
            }
            foreach ($this->allProducts as &$product) {
                if ($product["id"] == $userId) {
                    $product["units"] = $userUpdate;
                    $product["updatedAt"] = Carbon::now()->toDateTimeString();
                    echo "Product updated successfully!" . PHP_EOL;
                    $this->changesLog("Updated", $product["id"], $product["name"], $userUpdate);
                    break;
                }
            }
            break;
        }
    }

    public function delete(): void
    {
        $userId = (int)readline("Enter product ID: ");
        foreach ($this->allProducts as $key => $product) {
            if ($product["id"] == $userId) {
                unset($this->allProducts[$key]);
                echo "Product deleted successfully!" . PHP_EOL;
                $this->changesLog("Deleted", $product["id"], $product["name"], $product["units"]);
                break;
            }
        }
    }

    public function changesLog(string $action, string $id, string $name, int $units): void
    {
        $this->changesMade[] = [
            "username" => $this->user->getUsername(),
            "action" => $action,
            "id" => $id,
            "name" => $name,
            "units" => $units,
            "updatedAt" => Carbon::now()->toDateTimeString()
        ];
    }
    public function report(): void
    {
        $totalProducts = 0;
        $totalValue = 0;
        foreach ($this->allProducts as $product) {
            $totalProducts += $product["units"];
            $totalValue += $product["price"];
        }
        $totalValue = number_format($totalValue / 100, 2);
        echo "****************************" . PHP_EOL;
        echo "Warehouse products in total: $totalProducts" . PHP_EOL;
        echo "Total value of all products: $totalValue €" . PHP_EOL;
        echo "****************************" . PHP_EOL;
    }
    public function save(string $productJsonFile, string $changesJsonFile): void
    {
        file_put_contents($productJsonFile, json_encode($this->allProducts, JSON_PRETTY_PRINT));
        file_put_contents($changesJsonFile, json_encode($this->changesMade, JSON_PRETTY_PRINT));
    }

    public function load(string $productsJsonFile, string $changesJsonFile): void
    {
        $this->allProducts = json_decode(file_get_contents($productsJsonFile), true);
        $this->changesMade = json_decode(file_get_contents($changesJsonFile), true);
    }

    public function getAllProducts(): array
    {
        return $this->allProducts;
    }

    public function getChangesMade(): array
    {
        return $this->changesMade;
    }

}
