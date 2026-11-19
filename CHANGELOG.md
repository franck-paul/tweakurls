# Changelog

## 10.0 — 2026-11-08

- Use typed getter/setter Cursor method, DC 2.40+
- Enforces some verification before registering module

## 9.0 — 2026-08-03

- Avoid using magic methods for user preferences and blog settings (2.39+)
- Use MetaRecord::strField(), MetaRecord::intField() and MetaRecord::boolField() whenever is possible

## 8.2.1 — 2026-06-12

- Fix category management
- Harmonize usage of App::blogWorkspace()::NS_... and App::userWorkspace()::WS_... (and use short version of constant name for types)

## 8.2 — 2026-02-23

- Version de maintenance, sans changement fonctionnel notable relevé dans l’historique.

## 8.1 — 2025-09-22

- Use backend services rather than static calls

## 8.0.1 — 2025-09-11

- Review context checking: limit management to admin only, other backend ops for everybody

## 8.0 — 2025-09-07

- DC 2.36 compliance

## 7.0 — 2025-05-05

- Version de maintenance, sans changement fonctionnel notable relevé dans l’historique.

## 6.3.1 — 2024-12-14

- Ensure post_url is a string

## 6.3 — 2024-08-21

- Version de maintenance, sans changement fonctionnel notable relevé dans l’historique.

## 6.2 — 2024-08-21

- Version de maintenance, sans changement fonctionnel notable relevé dans l’historique.

## 6.1 — 2023-10-27

- Version de maintenance, sans changement fonctionnel notable relevé dans l’historique.

## 6.0 — 2023-10-24

- Use instanced item constants rather than interface ones
- 2.28 compliance

## 5.0.2 — 2023-10-03

- Don't use proxy if possible

## 5.0.1 — 2023-09-06

- Fix actions on entries

## 5.0 — 2023-08-26

- Dotclear 2.27 compliance, PHP 8.1+

## 4.4 — 2023-07-14

- Add samples

## 4.3.3 — 2023-07-14

- Forgot to use new names of settings items, my bad :-p

## 4.3.2 — 2023-06-27

- Add local static $init variable

## 4.3.1 — 2023-06-22

- Version de maintenance, sans changement fonctionnel notable relevé dans l’historique.

## 4.3 — 2023-06-22

- Version de maintenance, sans changement fonctionnel notable relevé dans l’historique.

## 4.2 — 2023-05-15

- Version de maintenance, sans changement fonctionnel notable relevé dans l’historique.

## 4.1 — 2023-05-10

- Even better
- Use My::id() if possible
- Fix return type

## 4.0 — 2023-04-29

- Prepare 2.26

## 3.0 — 2023-02-25

- Fix tweaking machine in some contexts
- Use new classes for actions
- Prepare 2.25

## 2.0 — 2022-11-30

- Dotclear 2.24 compliance
- Admin actions V2
- Remove unused args ($core, …)

## 1.2.1 — 2022-08-14

- Usual oups :-)

## 1.2 — 2022-08-10

- Update default branch in _define.php for alternative repository
- Add alternative repository

## 1.1 — 2020-06-25

- Allow usage for 'simple' users
- Add support URL
- Cleanup afert switching from hg
- short notation for array (array() → [])

## 1.0 — 2016-02-14

- 0.9, actions revamped, cosmetic
- 0.8.1 to be released on DA
