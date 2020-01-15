@product @attribute
Feature: Edit attributes
    In order to manage attributes for product variants
    As an administrator
    I need to be able to edit attributes

    Scenario: Edit the attribute
        Given I am logged in as an administrator
        When I go to "ekyna_product_attribute_choice_admin_edit" route with "attributeGroupId:1,attributeChoiceId:1"
        And I fill in "attribute[name]" with "Noir"
        And I fill in "attribute[translations][fr][title]" with "Noir"
        And I press "attribute_actions_save"
        Then I should see the resource saved confirmation message
        And I should see "Noir"
