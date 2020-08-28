Плагин для учета и публикации диссертаций на сайте [pstu.edu](https://patu.edu/)



## Шорткоды

### Диссертации

Аттрибуты шорткодов:
- **post_id** - идентификатор диссертации, в шаблоне диссертации определяется автоматически
- **empty** - строка, которая отображается если данные не найдены, по умолчанию `-`

- `[dissertation_public_author]` - автор
- `[dissertation_public_publication]` - дата публикации на сайте
- `[dissertation_public_protection]` - дата защиты
- `[dissertation_public_protection_time]` - время защиты
- `[dissertation_public_file_link]` - ссылка на файл диссертации
- `[dissertation_public_abstract_link]` - ссылка на файл автореферата
- `[dissertation_public_opponents]` - оппоненты

### Научные советы

Аттрибуты шорткодов:
- **term_id** - идентификатор научного совета
- **empty** - строка, которая отображается если данные не найдены, по умолчанию `-`

- `[science_counsil_public_list_of_posts]` - список диссертаций научного совета



## Шаблоны файлов

В прагине можно использовать свои шаблоны вывода. Шаблоны располагаются: <code>'директория текущей темы'/pstu_dissertation/</code>

Файлы шаблонов:

- **single-content-dissertation.php** - шаблон вывода содержимого диссертации на страницах single.php