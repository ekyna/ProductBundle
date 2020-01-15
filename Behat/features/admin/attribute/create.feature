@product @attribute
Feature: Create attributes
    In order to manage attributes for product variants
    As an administrator
    I need to be able to create new attributes

    @current
    Scenario: Create an attribute
        Given I am logged in as an administrator
        When I go to "ekyna_product_attribute_admin_new" route with "type:select"
        And I fill in "attribute[name]" with "Test attribute"
        And I click "attribute_translations_en"
        And I fill in "attribute[translations][en][title]" with "Test attribute"
        And I press "attribute_actions_save"
        Then I should see the resource saved confirmation message
        And I should see "Test attribute"
