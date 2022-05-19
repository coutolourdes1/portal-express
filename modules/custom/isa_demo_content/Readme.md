## Usage


Arguments:
- module: The name of the module.
- aliases: dcem
- required-arguments: 1

Example:
```bash
drush dcem isa_demo_content
```

And add the UUID of entities in my_default_content_module.info.yml file.

```
default_content:
  node:
    - c9a89616-7057-4971-8337-555e425ed782
    - b6d6d9fd-4f28-4918-b100-ffcfb15c9374
  file:
    - 59674274-f1f5-4d6a-be00-fecedfde6534
    - 0fab901d-36ba-4bfd-9b00-d6617ffc2f1f
  media:
    - ee63912a-6276-4081-93af-63ca66285594
    - bcb3c719-e266-45c1-8b90-8f630f86dcc7
  menu_link_content:
    - 9fbb684c-156d-49d6-b24b-755501b434e6
    - 19f38567-4051-4682-bf00-a4f19de48a01
  block_content:
    - af171e09-fcb2-4d93-a94d-77dc61aab213
    - a608987c-1b74-442b-b900-a54f40cda661
```
