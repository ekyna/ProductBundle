@product @attribute
Feature: Remove attributes
    In order to manage attributes for product variants
    As an administrator
    I need to be able to remove attributes

    Background:
        Given I am logged in as an administrator
        And The following attribute groups:
            | name    |
            | Couleur |
        And The following attributes:
            | name  | group   |
            | Blanc | Couleur |

    Scenario: Remove an attribute
        When I go to "ekyna_product_attribute_admin_remove" route with "attributeGroupId:1,attributeId:1"
        And I check "form[confirm]"
        And I press "form[actions][remove]"
        Then I should see the resource removed confirmation message
        And I should not see "Blanc"

    # TODO Scenario: Remove an attribute used by a product
