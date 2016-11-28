Feature: Create simple products
    In order to display products
    As an administrator
    I need to be able to create new simple products

    Background:
        Given I am logged in as an administrator
        And The following brands:
            | name    |
            | Apple   |
        And The following categories:
            | name        |
            | Smartphones |

#    @javascript
    Scenario: Create a simple product
        When I go to "ekyna_product_product_admin_new" route with "type:simple"

        And I fill in "product[designation]" with "iPhone 6"
        And I select "Apple" from "product[brand]"
        And I select "Smartphones" from "product[categories][]"
        And I fill in "product[reference]" with "APPL-IPHO-6"
        And I fill in "product[weight]" with "0.3"

#        And I click the '#product_references button[collection-role="add"]' element
#        And I select "Code EAN 13" from "product[references][0][type]"
#        And I fill in "product[references][0][type]" with "0888462064002"

        And I fill in "product[translations][fr][title]" with "iPhone 6"
        And I fill in "product[translations][fr][description]" with "<p>Such an awesome smartphone !</p>"

        And I fill in "product[netPrice]" with "549.16667"

        And I fill in "product[seo][translations][fr][title]" with "iPhone 6"
        And I fill in "product[seo][translations][fr][description]" with "<p>Such an awesome smartphone !</p>"


        And I press "product_actions_save"
        Then I should see "La ressource a été sauvegardée avec succès"
        And I should see "iPhone 6"
