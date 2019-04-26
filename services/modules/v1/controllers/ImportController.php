<?php
namespace services\modules\v1\controllers;

use services\modules\v1\models\Import;
use services\modules\v1\models\User;
use yii\base\Exception;
use yii\db\Expression;
use yii\web\UploadedFile;

class ImportController extends BaseController
{
    public $model;

    public function actionOrders()
    {
        $this->model = new Import();
        $file = new UploadedFile();
        $this->model->file = $file->getInstanceByName('file');

        if ($this->model->file) {
            date_default_timezone_set("Asia/Karachi");
            $this->model->createLogFile();
            $time = time();
            $this->model->file->saveAs('services/modules/v1/importedOrders/' . $time . '.' . $this->model->file->extension);
            $this->model->file = 'services/modules/v1/importedOrders/' . $time . '.' . $this->model->file->extension;
            $handle = fopen($this->model->file, "rw");
            $header = null;

            while (($fileop = fgetcsv($handle, 1000, ",")) !== false) {
                if ($header === null) {
                    $header = $fileop;
                    $this->model->validateOrdersCSVFile($header);
                    continue;
                }

                $this->model->executeOrderTransactionQueries($fileop);
            }

            fclose($handle);
        } else {
            throw new Exception('Please attach file.');
        }

    }
}
 
 