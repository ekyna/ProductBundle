Feature: Product admin
  I need to be able to visit the home page

  Scenario: I can see the title of the home page
    When I go to the homepage
    Then I should see "Accueil" in the "title" element

  Scenario: I can't see hello world
    When I go to "/contact"
    Then I should not see "Accueil" in the "title" element
