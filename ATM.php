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

    private function enterCard(){
        $data = NULL;
        while ($data != "Y") {
            echo "Для того чтобы вставить карту, нажмите Y \n";
            $data = trim(fgets(STDIN));
        }
        $this->card = new Card("0000", 10000);
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

    public function authorization(): bool{
        fwrite(STDOUT, "Введите пароль: \n");
        $password = trim(fgets(STDIN));

        if ($password === $this->card->password) {
            return true;
        } else {
            echo " ОШИБКА: пароль неправильный. Повторите ввод \n";
            return false;
        }
    }

    public function menu(){
        fwrite(STDOUT, "Выбирите действие: \n 1. Снять наличные\n 2. Проверить баланс карты\n 3. Пополнить мобильный счет\n 0. Выход\n");
        $actionMenu = trim(fgets(STDIN));
        if($actionMenu > 3){
            echo " ОШИБКА: отсутсвует данный пункт меню\n";
            $this->menu();
        }
        switch ($actionMenu) {
            case 1:
                print ($this->getMoney());
                $this->menu();
            case 2:
                print("Баланс карты:\n $ ".$this->card->money."\n");
                $this->menu();
            case 3:
                print($this->topUpPhoneAccount());
                $this->menu();
            case 0:
                echo "Выход\n";
                $this->start();
        }
    }

    public function getMoney(){
        fwrite(STDOUT, "Выбирите сумму:\n 1. $50\n 2. $100\n 3. $200\n 4. $500\n 5. $1000\n 0. Другая сумма\n");
        $actionMenu = trim(fgets(STDIN));

        switch ($actionMenu) {
            case 1:
                $this->getBills(50);
                $this->moneyInAtm-=50;
                $this->card->money-=50;
                $this->menu();
            case 2:
                $this->getBills(100);
                $this->moneyInAtm-=100;
                $this->card->money-=100;
                $this->menu();
            case 3:
                $this->getBills(200);
                $this->moneyInAtm-=200;
                $this->card->money-=200;
                $this->menu();
            case 4:
                $this->getBills(500);
                $this->moneyInAtm-=500;
                $this->card->money-=500;
                $this->menu();
            case 5:
                $this->getBills(1000);
                $this->moneyInAtm-=1000;
                $this->card->money-=1000;
                $this->menu();
            case 0:
                $this->anotherAmountOfMoney();
                $this->menu();
        }
    }

    private function anotherAmountOfMoney(){
        fwrite(STDOUT, "Введите сумму кратную 50:\n");
        $output = trim(fgets(STDIN));
        if($output > $this->moneyInAtm){
            print(" ОШИБКА: АТМ не может выдать эту сумму. Доступное количество: ".$this->moneyInAtm."\n");
        } else if( $output > $this->card->money){
            print (" ОШИБКА: недостаточно денег на счету!\n");
        }
        else {
            if($output %50 == 0){
                $this->getBills($output);
                $this->moneyInAtm-=$output;
                $this->card->money-=$output;
            }
            else{
                echo "ОШИБКА: не верно введена сумма!\n";
                $this->getMoney();
            }
        }
    }

    private function getBills($withdrawAmount)
    {
        $this->cash = array();
        $this->money_left = $withdrawAmount;
        $this->moneyInAtm -= $withdrawAmount;
        while ($this->money_left > 0) {
            if ($this->money_left < min($this->bills)) {
                throw new WithdrawException("Эта сумма не подлежит оплате. \n");
            }
            $bill = $this->configureBills();
            $this->cash[] = $bill;
            $this->money_left -= $bill;
        }
        echo "Заберите деньги: \n";
        $money = array_count_values($this->cash);
        foreach ($money as $key => $value){
            echo " Колличество купюр по $$key равно $value \n";
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

    private function topUpPhoneAccount(){
        fwrite(STDOUT, "Введите номер телефона: +38");
        $numberPhone = trim(fgets(STDIN));
        if(!preg_match("/[0-9]{10}/i", $numberPhone)){
            echo " ОШИБКА: введенный мобильный телефон не соответствует шаблону, пример: +380960969966 \n";
            $this->topUpPhoneAccount();
        }

        fwrite(STDOUT, " Введите сумму пополнения счета: ");
        $moneyTransfer = trim(fgets(STDIN));
        if($moneyTransfer > $this->card->money){
            echo " ОШИБКА: введенная сумма больше суммы на карте!\n";
            fwrite(STDOUT, "Выбирите действие:\n 1. Повторить пополнение счета\n 0. Вернуться в главное меню\n");
            $actionMenu = trim(fgets(STDIN));
            switch ($actionMenu) {
                case 1:
                    $this->topUpPhoneAccount();
                case 0:
                    $this->menu();
            }
        }
        else{
            echo " Номер телефона +380$numberPhone пополнен на $$moneyTransfer. Хорошего дня!\n";
            $this->card->money-=$moneyTransfer;
            $this->menu();
        }
    }
}

$atm = new ATM();
$atm->start();
//$atm->menu();
