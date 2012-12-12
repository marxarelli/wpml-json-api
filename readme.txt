=== WPML JSON API ===
Contributors: dzachary
Donate link: https://store.lettersandlight.org
Tags: json, api, i18n, cms, wpml, multilingual, translation
Requires at least: 2.8
Tested up to: 3.4
Stable tag: 0.1.3

An extension to JSON-API for sites using the WPML Multilingual CMS plugin.

== Description ==

This plugin filters the response content of requests made to the WordPress [JSON-API](http://wordpress.org/extend/plugins/json-api) to include data for translations created through the [WPML Multilingual CMS](http://wpml.org) plugin. One may also request the translation of response objects to a supported target language.

[The Office of Letters and Light](http://www.lettersandlight.org) uses this plugin to integrate WordPress and [WPML](http://wpml.org) with its Rails-built [National Novel Writing Month](http://www.nanowrimo.org) event-site application. We use WordPress to manage all back-end site content and our [Kiosk Ruby gem](http://github.com/lettersandlight/kiosk) for consuming, caching, searching, and serving the content through Rails. This could not have been so easily accomplished without the well-built and extensible [JSON-API](http://wordpress.org/extend/plugins/json-api) for which we are grateful. And without the [WPML](http://wpml.org) plugin, we would not have such an intuitive and capable CMS i18n interface.

== Installation ==

1. Upload the `wpml-json-api` folder to the `/wp-content/plugins/` directory or install directly through the plugin installer.
2. Activate the plugin through the 'Plugins' menu in WordPress or by using the link provided by the plugin installer.

== Usage ==

Please be familiar with the
[JSON-API](http://wordpress.org/extend/plugins/json-api). 

1. Augmented response objects
   * 1.1. Language
   * 1.2. Translations
2. Filtering response objects by language
   * 2.1. Specifying the language
   * 2.2. Filtered results
3. Response object translation
   * 3.1 Specifying the target language
   * 3.2 Translated results
   * 3.3 Translatable objects and properties

== 1. Augmented response objects ==

== 1.1. Language ==

After enabling the WPML JSON-API plugin, response objects will contain the
language in which the object content was written.

Let's take a look at an example response to a request made to `http://wordpress.site.example/api/get_page/?dev=1&id=127`.

    {
      "status": "ok",
      "page": {
        "id": 127,
        "type": "page",
        "slug": "the-best-page",
        "url": "http:\/\/wordpress.site.example\/the-best-page\/",
        "status": "publish",
        "title": "The Best Page",
        "title_plain": "The Best Page",
        "content": "<p>This is the best page ever created.<\/p>\n<blockquote><p>Best damn page ever. &#8211; Dan<\/p><\/blockquote>\n",
        "excerpt": "This is the best page ever created. Best damn page ever. &#8211; Dan",
        "date": "2011-09-22 18:48:09",
        "modified": "2011-09-22 18:54:55",
        "categories": [],
        "tags": [],
        "author": {
          "id": 4,
          "slug": "dzachary",
          "name": "Daniel Duvall",
          "first_name": "Daniel",
          "last_name": "Duvall",
          "nickname": "Daniel Duvall",
          "url": "",
          "description": ""
        },
        "comments": [],
        "attachments": [],
        "comment_count": 0,
        "comment_status": "open",
        "translations": {
          "en": {
            "post_title": "The Best Page",
            "post_status": "publish",
            "id": 21,
            "language_code": "en",
            "is_original": true,
            "resource_id": 127
          },
          "es": {
            "post_title": "La P\u00e1gina Mejor",
            "post_status": "publish",
            "id": 22,
            "language_code": "es",
            "is_original": false,
            "resource_id": 131
          }
        },
        "language": "en"
      }
    }

The `language` property specifies the code of the language in which the page
was written—pretty simple.

== 1.2. Translations ==

The `translations` objects of the same example response given in Section 1.1.
include data for each *published* resource that comprises a translation of
its parent object.

    "translations": {
      "en": {
        "post_title": "The Best Page",
        "post_status": "publish",
        "id": 21,
        "language_code": "en",
        "is_original": true,
        "resource_id": 127
      },
      "es": {
        "post_title": "La P\u00e1gina Mejor",
        "post_status": "publish",
        "id": 22,
        "language_code": "es",
        "is_original": false,
        "resource_id": 131
      }


Properties included in each member of the `translations` object depend on the
type of parent object.

All `post` and `page` objects will include the following.

* `id` - The ID of the translation object.
* `language_code` - The code of the language in which the translation is written. Each translation object is also keyed by this value.
* `is_original` - Specifies whether the language of this translation is the language in which the original work was written.
* `resource_id` - The ID of the translated version of the parent object.
* `post_title` - The title of the translated post/page.
* `post_status` - The status of the translated post/page. Always 'publish'.
* `post_id` - Redundancy of `resource_id`.

All `category` objects will include the following.

* `id` - The ID of the translation object.
* `language_code` - The code of the language to which the category was translated.
* `is_original` - Specifies whether the language of this translation is the language of the original category.
* `resource_id` - The ID of the translated version of the parent object.
* `name` - The translated category name.
* `term_id` - Redundancy of `resource_id`.
* `post_count` - The number of posts tagged with the translated category.

== 2. Filtering response objects by language ==

You may tell the API to only return objects for a specific language by using
the `language` parameter.

== 2.1. Specifying the language ==

The `language` parameter should be one of the supported language codes.

Example:

* `http://wordpress.site.example/api/get_category_index/?language=es&dev=1`

== 2.2. Filtered results ==

Following is a example response to the example request in Section 2.1.

    {
      "status": "ok",
      "count": 2,
      "categories": [
        {
          "id": 13,
          "slug": "noticias-de-ultima-hora-2",
          "title": "Noticias de \u00daltima Hora",
          "description": "",
          "parent": 0,
          "post_count": 1,
          "translations": {
            "en": {
              "name": "Breaking News",
              "term_id": 4,
              "post_count": 2,
              "id": 9,
              "language_code": "en",
              "is_original": true,
              "resource_id": 4
            },
            "es": {
              "name": "Noticias de \u00daltima Hora",
              "term_id": 13,
              "post_count": 1,
              "id": 26,
              "language_code": "es",
              "is_original": false,
              "resource_id": 13
            }
          },
          "language": "es"
        }
      ]
    }

== 3. Response object translation ==

You can request objects to be translated before they are returned. This may
save you some requests if you have the slug or ID and just need the object in
a specific language.

== 3.1. Specifying the target language ==

The target language can be specified by passing a `to_language` parameter. The
value should be one of the supported language codes.

Example:

* `http://wordpress.site.example/api/get_page/?slug=the-best-page&to_language=es&dev=1`

== 3.2. Translated results ==

Following is an example response to the example request in Section 3.1.

    {
      "status": "ok",
      "page": {
        "id": 127,
        "type": "page",
        "slug": "la-pagina-mejor",
        "url": "http:\/\/wordpress.site.example\/the-best-page\/",
        "status": "publish",
        "title": "La P\u00e1gina Mejor",
        "title_plain": "La P\u00e1gina Mejor",
        "content": "<p>Esta es la p\u00e1gina mejor se haya creado.<\/p>\n<blockquote><p>Best damn page ever. &#8211; Dan<\/p><\/blockquote>\n",
        "excerpt": "Esta es la p\u00e1gina mejor se haya creado. Best damn page ever. &#8211; Dan",
        "date": "2011-09-22 18:48:09",
        "modified": "2011-09-22 18:54:55",
        "categories": [],
        "tags": [],
        "author": {
          "id": 4,
          "slug": "dzachary",
          "name": "Daniel Duvall",
          "first_name": "Daniel",
          "last_name": "Duvall",
          "nickname": "Daniel Duvall",
          "url": "",
          "description": ""
        },
        "comments": [],
        "attachments": [],
        "comment_count": 0,
        "comment_status": "open",
        "translations": {
          "en": {
            "post_id": 127,
            "post_title": "The Best Page",
            "post_status": "publish",
            "id": 21,
            "language_code": "en",
            "is_original": true,
            "resource_id": 127
          },
          "es": {
            "post_id": 131,
            "post_title": "La P\u00e1gina Mejor",
            "post_status": "publish",
            "id": 22,
            "language_code": "es",
            "is_original": false,
            "resource_id": 131
          }
        },
        "language": "es",
        "translated": true,
        "original_slug": "the-best-page",
        "original_title": "The Best Page",
        "original_title_plain": "The Best Page"
      }
    }

As you can see the `slug`, `title`, `title_plain`, `excerpt`, and `content`
properties have been rewritten with the Spanish translation. In addition, the
original properties have been prefixed with `original_`. This is always the
case for all properties except for `excerpt` and `content`. In an effort to
save on response size, the latter have been omitted.

== 3.3. Translatable objects and properties ==

Here are all of the translatable object types and their translatable
properties, those that can be rewritten as described in Section 3.2.

Objects `post` and `page`:

* `slug`
* `title`
* `title_plain`
* `excerpt`
* `content`

Objects `tag` and `category`:

* `slug`
* `title`
* `description`

== Changelog ==

Please see the [GitHub project page](https://github.com/marxarelli/wpml-json-api) for a comprehensive list of changes.
