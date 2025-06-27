# User Words

A user-page magic word extension

## Added Magic Words

Magic words require being in the `User:` namespace. If used in any other namespace, the magic words will return a blank value.

* `{{USERLANGUAGECODE}}`
  * Shows the users language code set in their preferences, or the wikis default language
* `{{USERFIRSTREVISIONSTAMP}}`
  * Timestamp of the users first edit
* `{{USERGROUPS:all}}`
  * Groups that the user is in, comma separated
  * `all` parameter takes a truthy input, when true will show implicit and explicit groups. Otherwise only explicit
* `{{USERREGISTRATIONSTAMP}}`
  * Timestamp of when the user registered