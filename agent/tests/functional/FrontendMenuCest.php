<?php

namespace agent\tests\functional;

use backend\models\AgentUser;
use backend\tests\FunctionalTester;
use backend\fixtures\UserFixture;
use yii\helpers\Url;

/**
 * Class FrontendMenuCest
 */
class FrontendMenuCest
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
    }

    public function checkIndex(FunctionalTester $I)
    {
        $I->amOnPage(Url::toRoute('/agent-menu/index'));
        $I->see('前台菜单');
        $I->click("a[title=编辑]");
        $I->see("编辑前台菜单");
        $I->fillField("Menu[name]", '测试123');
        $I->submitForm("button[type=submit]", []);
        $I->seeInField("Menu[name]", "测试123");
    }
}
