<?php

namespace BusinessLogic\Domain\Translation\Models;

use Unzer\Core\BusinessLogic\Domain\Translations\Exceptions\InvalidTranslatableArrayException;
use Unzer\Core\BusinessLogic\Domain\Translations\Model\Translation;
use Unzer\Core\BusinessLogic\Domain\Translations\Model\TranslationCollection;
use Unzer\Core\Tests\BusinessLogic\Common\BaseTestCase;

class TranslationCollectionModelTest extends BaseTestCase
{
    /**
     * @return void
     */
    public function testGetDefault()
    {
        //arrange
        $defaultTranslation = new Translation("en", "Shop");
        $translationCollection = new TranslationCollection($defaultTranslation);

        //act
        $result = $translationCollection->getDefaultTranslation();

        //assert
        self::assertEquals($defaultTranslation, $result);
    }

    /**
     * @return void
     */
    public function testGetTranslation()
    {
        //arrange
        $translationCollection = new TranslationCollection(new Translation("en", "Shop"));

        $translationCollection->addTranslation(new Translation("de", "Shop"));

        $newTranslation = new Translation("de", "Shop1");
        $translationCollection->addTranslation($newTranslation);
        $result = $translationCollection->getTranslation("de");

        //assert
        self::assertEquals($newTranslation, $result);
    }

    /**
     * @return void
     */
    public function testGetTranslationMessage()
    {
        $translationCollection = new TranslationCollection(new Translation("en", "Shop"));

        $translationCollection->addTranslation(new Translation("de", "Shop"));

        $newTranslation = new Translation("de", "Shop1");
        $translationCollection->addTranslation($newTranslation);
        $result = $translationCollection->getTranslationMessage("de");

        self::assertEquals("Shop1", $result);
    }

    /**
     * @return void
     *
     * @throws InvalidTranslatableArrayException
     */
    public function testInvalidTranslation()
    {
        $this->expectException(InvalidTranslatableArrayException::class);

        TranslationCollection::fromArray([["en"=>"name"]]);
    }

    /**
     * @return void
     * @throws InvalidTranslatableArrayException
     */
    public function testInvalidTranslationNoCollection()
    {
        $this->expectException(InvalidTranslatableArrayException::class);

        TranslationCollection::fromArray(["en"=>"name"]);
    }

}