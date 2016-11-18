Feature: Edit attributes
    In order to manage attributes for product variants
    As an administrator
    I need to be able to edit attributes

    Background:
        Given I am logged in as an administrator
        And The following attribute groups:
            | name    |
            | Couleur |
        And The following attributes:
            | name  | group   |
            | Blanc | Couleur |

    Scenario: Edit the attribute
        When I go to "ekyna_product_attribute_admin_edit" route with "attributeGroupId:1,attributeId:1"
        And I fill in "attribute[name]" with "Noir"
        And I fill in "attribute[translations][fr][title]" with "Noir"
        And I press "attribute_actions_save"
        Then I should see "La ressource a été sauvegardée avec succès"
        And I should see "Noir"
