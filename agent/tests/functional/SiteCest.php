<?php

namespace agent\tests\functional;

use backend\models\AgentUser;
use backend\tests\FunctionalTester;
use backend\fixtures\UserFixture;
use yii\helpers\Url;

/**
 * Class SiteCest
 */
class SiteCest
{

    public function _fixtures()
    {
        return [
            'user' => [
                'class' => UserFixture::class,
                'dataFile' => codecept_data_dir() . 'login_data.php'
            ]
        ];
    }

    public function _before(FunctionalTester $I)
    {
        $I->amLoggedInAs(AgentUser::findIdentity(1));
        $I->amOnRoute('setting/website');
    }

    public function checkError(FunctionalTester $I)
    {
        $I->amOnPage(Url::toRoute('/site/error'));
        $I->see("404");
    }

}
