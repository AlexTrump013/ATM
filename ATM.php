<?php
include("card.php");
class ATM
{
    private $bills = array(50, 100, 200, 500, 1000);
    private $money_left;
    private $moneyInAtm = 1000000;
    private $cash = array();
    private $card;

    function __construct()
    {
        rsort($this->bills);
    }

    public function authorization(): bool{
        fwrite(STDOUT, "Enter your password: \n");
        $password = trim(fgets(STDIN));

        if ($password === $this->card->password) {
            echo "Пароль правильный! \n ";
            return true;
        } else {
            echo "Пароль неправильный. Повторите ввод \n ";
            return false;
        }
    }

    private function enterCard(){
        $data = NULL;
        while ($data != "Y") {
            echo "Для того чтобы вставить карту, нажмите Y \n";
            $data = trim(fgets(STDIN));
        }
        $this->card = new Card("0000", 10000);
    }

    public function menu(){
        fwrite(STDOUT, "Выбирите действие: \n 1 Снять наличные\n 2 Проверить баланс карты\n 0 Выход\n");
        $actionMenu = trim(fgets(STDIN));

        switch ($actionMenu) {
            case 1:
                print ($this->getMoney());
                $this->menu();
            case 2:
                print("Баланс карты:\n $ ".$this->card->money."\n");
                $this->menu();
            case 0:
                echo "Выход\n";
                $this->start();
        }
    }

    public function start(){
        $this->enterCard();
        $enterPasswordTimes = 0;
        $notAuthorize = true;
        while ($notAuthorize){
            if($enterPasswordTimes < 3) {
                $notAuthorize = !$this->authorization();
                if ($notAuthorize == true) $enterPasswordTimes++;
            } else {
                printf("Вы ввели неправильный пин-код 3 раза. Ваша карта заблокорованна! \n");
                $this->start();
            }
        }
        $this->menu();
    }

    public function getMoney(){
        fwrite(STDOUT, "Введите сумму:\n");
        $output = trim(fgets(STDIN));
        if($output > $this->moneyInAtm){
            print("АТМ не может выдать эту сумму. Доступное количество: ".$this->moneyInAtm."\n");

        } else if( $output > $this->card->money){
            print ("Недостаточно денег на счету!\n");
        } else {
            print($this->getBills($output));
            $this->moneyInAtm-=$output;
            $this->card->money-=$output;
        }
    }

    private function getBills($withdrawAmount)
    {
        $this->cash = array();
        $this->money_left = $withdrawAmount;
        $this->moneyInAtm -= $withdrawAmount;
        while ($this->money_left > 0) {
            if ($this->money_left < min($this->bills)) {
                throw new WithdrawException("This amount cannot be paid. \n");
            }
            $bill = $this->configureBills();
            $this->cash[] = $bill;
            $this->money_left -= $bill;
        }
        echo "Заберите деньги: \n";
        $money = array_count_values($this->cash);
        foreach ($money as $key => $value){
            echo "Колличество купюр по $: $key равно $value \n";
        }
    }

    private function configureBills()
    {
        foreach ($this->bills as $bill) {
            $division = $this->money_left / $bill;
            $rest = $this->money_left % $bill;
            if (($division >= 1) && ($rest > (min($this->bills) + 1) || ($rest === min($this->bills)) || ($rest === 0))) {
                return $bill;
            }
        }
        return min($this->bills);
    }
}

$atm = new ATM();
$atm->start();
