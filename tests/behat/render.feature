@filter @filter_sheetmusic
Feature: Sheet music renders wherever Moodle displays text
  In order to read notation in course content
  As a student
  I need stored scores to be engraved, labelled for screen readers, and readable even when they are not

  # Newlines inside a score are written as the numeric character reference &#10;, because a
  # Gherkin table cell cannot hold a literal line break. HTMLPurifier decodes it back to a real
  # newline before the filter ever sees it, so what is stored is byte-identical to what the
  # editor writes (RELATIONS.md section A2 rule 5). Bar lines are escaped as \| for the same
  # reason: an unescaped pipe would end the table cell.
  #
  # The engraved notation is asserted with an xpath_element, not ".sheetmusic-render svg". Mink
  # converts a CSS locator to XPath, and XPath 1.0 name tests are namespace aware, so a bare
  # `svg` step never matches an <svg> element (it lives in the SVG namespace). local-name() is
  # namespace blind and does match. Measured 2026-08-21: the CSS form failed against a page that
  # demonstrably contained the rendered SVG.
  Background:
    Given the following "courses" exist:
      | fullname | shortname | format |
      | Music 1  | MUS1      | topics |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | student1 | Sam       | Student  | student1@example.com |
    And the following "course enrolments" exist:
      | user     | course | role    |
      | student1 | MUS1   | student |
    And the "sheetmusic" filter is "on"

  @javascript
  Scenario: A score in a page activity is engraved as notation
    Given the following "activities" exist:
      | activity | course | name        | content                                                                                                      | contentformat |
      | page     | MUS1   | Scale study | <pre class="sheetmusic sheetmusic-abc">X:1&#10;M:4/4&#10;L:1/8&#10;K:G&#10;\|GABc dedB\|c4 A4\|</pre>         | 1             |
    When I am on the "Scale study" "page activity" page logged in as "student1"
    Then I wait until "//div[contains(concat(' ', normalize-space(@class), ' '), ' sheetmusic-render ')]/*[local-name() = 'svg']" "xpath_element" exists
    And ".sheetmusic-render" "css_element" should exist

  @javascript
  Scenario: A score in a forum post is engraved as notation
    Given the following "activities" exist:
      | activity | course | name       | idnumber |
      | forum    | MUS1   | Score talk | forum1   |
    And the following "mod_forum > discussions" exist:
      | forum  | user     | name          | subject       | message                                                                                              | messageformat |
      | forum1 | student1 | Rhythm puzzle | Rhythm puzzle | <pre class="sheetmusic sheetmusic-abc">X:1&#10;M:3/4&#10;L:1/8&#10;K:D&#10;\|DEFG AB\|d6\|</pre>      | 1             |
    When I am on the "Score talk" "forum activity" page logged in as "student1"
    And I follow "Rhythm puzzle"
    Then I wait until "//div[contains(concat(' ', normalize-space(@class), ' '), ' sheetmusic-render ')]/*[local-name() = 'svg']" "xpath_element" exists
    And ".sheetmusic-render" "css_element" should exist

  @javascript
  Scenario: The engraved score carries an accessible label
    Given the following "activities" exist:
      | activity | course | name        | content                                                                                              | contentformat |
      | page     | MUS1   | Scale study | <pre class="sheetmusic sheetmusic-abc">X:1&#10;M:4/4&#10;L:1/8&#10;K:G&#10;\|GABc dedB\|</pre>        | 1             |
    When I am on the "Scale study" "page activity" page logged in as "student1"
    Then I wait until ".sheetmusic-render" "css_element" exists
    And the "role" attribute of ".sheetmusic-render" "css_element" should contain "img"
    And the "aria-label" attribute of ".sheetmusic-render" "css_element" should contain "Sheet music"
    And the "aria-label" attribute of ".sheetmusic-render" "css_element" should contain "key G"

  # No @javascript: graceful degradation is a server-side property and must hold with no JS at all.
  Scenario: With the filter switched off the source stays on the page and readable
    Given the "sheetmusic" filter is "off"
    And the following "activities" exist:
      | activity | course | name        | content                                                                                              | contentformat |
      | page     | MUS1   | Scale study | <pre class="sheetmusic sheetmusic-abc">X:1&#10;M:4/4&#10;L:1/8&#10;K:G&#10;\|GABc dedB\|</pre>        | 1             |
    When I am on the "Scale study" "page activity" page logged in as "student1"
    Then I should see "K:G"
    And I should see "M:4/4"
    And ".sheetmusic-block" "css_element" should not exist
