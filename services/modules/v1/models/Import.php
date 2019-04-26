<?php
namespace services\modules\v1\models;

use yii\base\Exception;
use yii\base\Model;
use yii\db\Expression;

class Import extends Base
{
    public $logFile;
    public $conn;
    public $file;
    public $connection;
    public $transaction;


    /**
     * Set database connection
     * @return \yii\db\Connection
     */
    public function _connection()
    {
        return $this->connection = \Yii::$app->db;
    }


    /**
     * set account handler formate
     * @param $accountHandler
     * @return string
     */
    public function setAccountHandler($accountHandler)
    {
        $number = str_replace('-', '', $accountHandler); //remove hypen from account_handler (mobile number)
        $newNumber = substr($number, 0, 1); //get first digit of the number
        if ($newNumber == 0) {
            $number = substr($number, 1); //if first digit it zero than remove it
        }
        return '+92' . $number; //add +92 with number to make standard formate
    }


    /**
     * create log file which contains complete log details of import process
     */
    public function createLogFile()
    {
        $path = 'services/modules/v1/ImportOrder_logs';

        if (!file_exists($path)) {
            mkdir($path); //make new directory if does not exist
            chmod($path, 0777); //grant read write permission to the folder.
        }

        $this->logFile = $path . '/import_orders_' . date('Ymd_His', time()) . '.txt';
        $this->conn = fopen($this->logFile, "a+");
        chmod($this->logFile, 0777);
        $this->logIt('INFO: Log file created at ' . date('m-d-Y H:i:s', time()));
    }

    /**
     * write log message into log file
     * @param $message
     */
    public function logIt($message)
    {
        $logentry = date('m-d-Y H:i:s', time()) . '   ' . $message . PHP_EOL;
        echo $logentry;
        fwrite($this->conn, $logentry);
    }

    /**
     * validate CSV files columns, column names must be exactly save as defined below example to avoid invalid data insertion.
     * @param $columns
     * @return bool
     */
    public function validateOrdersCSVFile($columns)
    {
        $this->logIt('INFO: Validating CSV file formate.');
        if (trim($columns[0]) == 'Date and Time' && trim($columns[1]) == 'Customer Name' && trim($columns[2]) == 'Customer Contact Number' &&
            trim($columns[3]) == 'Source Location (Pickup Address)' && trim($columns[4]) == 'Destination Location (Delivery Address)' &&
            trim($columns[5]) == 'Recipient Name' && trim($columns[6]) == 'Recipient Contact Number' && trim($columns[7]) == 'Order Details' &&
            trim($columns[8]) == 'Customer Notes' && trim($columns[9]) == 'Agent Comments' && trim($columns[10]) == 'Rider Name' && trim($columns[11]) == 'Misc.' &&
            trim($columns[12]) == 'Order Amount' && trim($columns[13]) == 'Delivery Fee' && trim($columns[14]) == 'Commission'
        ) {
            $this->logIt('INFO: CSV file formate validated successfully.' . PHP_EOL);
            return true;
        }
        $this->logIt('INFO: Invalid CSV file formate.');
        $this->logIt('INFO: CSV file formate should be like below example.' . PHP_EOL . PHP_EOL . 'Date and Time' . PHP_EOL . 'Customer Name' . PHP_EOL . 'Source Location1 (Pickup Address)' . PHP_EOL . 'Customer Contact Number' . PHP_EOL);
        $this->logIt('INFO: ******* EXITING ********');
        exit;
    }


    /**
     * Create new oauth user if it does not exist else return already exist oauth user.
     * @param $accountHandler
     * @return mixed
     */
    public function handleOauthUser($accountHandler)
    {
        if (trim($accountHandler)) {
            $number = $this->setAccountHandler($accountHandler);
            $user = User::findOne(['account_handler' => $number]);
            if ($user) {
                //if user status is 2 (deleted) than create new with same account handler.
                if ($user['status'] == 2) {
                    $this->logIt('INFO: oauth_user[' . $number . '] does not exist hence creating new.');
                    $this->_connection()->createCommand()->insert('oauth_users', ['account_handler' => $number, 'status' => 0, 'created' => new Expression('NOW()'), 'modified' => new Expression('NOW()')])->execute();
                    $this->logIt('INFO: oauth_user [' . $number . '] created successfully with ID[' . $this->_connection()->getLastInsertID() . '].');
                    return $this->_connection()->getLastInsertID();
                }
                //else return already exist user.
                $this->logIt('INFO: oauth_user[' . $number . '] already exist hence returning its ID [' . $user['id'] . '].');
                return $user['id'];
            } else {
                //create new oauth user
                $this->logIt('INFO: oauth_user[' . $number . '] does not exist hence creating new.');
                $this->_connection()->createCommand()->insert('oauth_users', ['account_handler' => $number, 'status' => 0, 'created' => new Expression('NOW()'), 'modified' => new Expression('NOW()')])->execute();
                $this->logIt('INFO: oauth_user [' . $number . '] created successfully with ID[' . $this->_connection()->getLastInsertID() . '].');
                return $this->_connection()->getLastInsertID();
            }
        }
    }


    /**
     * Create new customer with oauth user id,  if it does not exist else return already exist customer id.
     * @param $customerName
     * @param $aouthId
     * @return mixed
     */
    public function handleCustomer($customerName, $aouthId)
    {
        if ($customerName) {
            $customer_id = (new \yii\db\Query())->select(['id'])->from('customer')->where(['oauth_user_id' => $aouthId])->one();
            if ($customer_id) {
                $this->logIt('INFO: Customer[' . $customerName . '] already exist hence returning its ID[' . $customer_id['id'] . '].');
                return $customer_id['id'];
            } else {
                $this->logIt('INFO: Customer[' . $customerName . '] does not exist hence creating new.');
                $this->_connection()->createCommand()->insert('customer', ['oauth_user_id' => $aouthId, 'name' => $customerName, 'created' => new Expression('NOW()'), 'modified' => new Expression('NOW()')])->execute();
                $this->logIt('INFO: Customer [' . $customerName . '] created successfully with ID[' . $this->_connection()->getLastInsertID() . '].');
                return $this->_connection()->getLastInsertID();
            }
        }
    }


    /**
     * Get Rider Id by its name
     * @param $riderName
     * @return mixed
     */
    public function getRider($riderName)
    {
        if ($riderName) {
            $rider_id = (new \yii\db\Query())->select(['id'])->from('rider')->where(['first_name' => $riderName])->one();
            if ($rider_id) {
                $this->logIt('INFO: Found Rider[' . $riderName . '] with ID[' . $rider_id['id'] . '].');
                return $rider_id['id'];
            }
        }
    }


    /**
     * Create new order
     * @param $data
     * @return mixed
     */
    public function createOrder($data)
    {
        if (count($data) > 0) {
            $this->logIt('INFO: Creating new order.');
            $this->_connection()->createCommand()->insert('job', $data)->execute();
            $this->logIt('INFO: New Order created successfully with ID[' . $this->_connection()->getLastInsertID() . '].');
            return $this->_connection()->getLastInsertID();
        }
    }


    /**
     * Create new task against order.
     * @param $data
     * @return mixed
     */
    public function createTask($data)
    {
        if (count($data) > 0) {
            $this->logIt('INFO: Now creating tasks.');
            $this->_connection()->createCommand()->insert('task', $data)->execute();
            $this->logIt('INFO: New Task created successfully with ID[' . $this->_connection()->getLastInsertID() . '].' . PHP_EOL);
            return $this->_connection()->getLastInsertID();
        }
    }


    /**
     * Execute all above methods to create order and task and users using transaction query.
     * @param $data
     */
    public function executeOrderTransactionQueries($data)
    {
        $this->transaction = $this->_connection()->beginTransaction();
        try {
            $oauth_user_id = $this->handleOauthUser($data[2]);
            if ($oauth_user_id) {
                $customer_id = $this->handleCustomer(ucfirst($data[1]), $oauth_user_id);
                $rider_id = $this->getRider(ucfirst($data[10]));
                $order = array(
                    'customer_id' => $customer_id,
                    'city_id' => 1,
                    'cc_agent_id' => 1,
                    'job_dispatcher_id' => 1,
                    'book_by' => 'agent',
                    'status' => 1,
                    'created' => new Expression('NOW()'),
                    'modified' => new Expression('NOW()')
                );

                $order_id = $this->createOrder($order);
                if ($order_id) {
                    $task = array(
                        'job_id' => $order_id,
                        'name' => $data[1],
                        'contact_name' => $data[5],
                        'contact_number' => $data[6],
                        'details' => $data[7],
                        'status' => 1,
                        'note' => $data[8],
                        'rider_id' => $rider_id,
                        'created_by' => 1,
                        'modified_by' => 1,
                        'created' => new Expression('NOW()'),
                        'modified' => new Expression('NOW()'),
                    );
                    $this->createTask($task);
                }
            }
            $this->transaction->commit();
        } catch (Exception $e) {
            $this->logIt('ERROR: There were an error in creating order hence rolling back due to [' . $e->getMessage() . '].' . PHP_EOL);
            $this->transaction->rollback();
        }
    }
}