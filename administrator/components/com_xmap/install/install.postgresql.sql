CREATE TABLE "#__xmap_sitemap" (
  "id" serial NOT NULL,
  "title" character varying(255) DEFAULT NULL,
  "alias" character varying(255) DEFAULT NULL,
  "introtext" text DEFAULT NULL,
  "metadesc" text DEFAULT NULL,
  "metakey" text DEFAULT NULL,
  "attribs" text DEFAULT NULL,
  "selections" text DEFAULT NULL,
  "excluded_items" text DEFAULT NULL,
  "is_default" integer DEFAULT 0,
  "state" integer DEFAULT NULL,
  "access" integer DEFAULT NULL,
  "created" timestamp without time zone DEFAULT '1970-01-01 00:00:00' NOT NULL,
  "count_xml" integer DEFAULT NULL,
  "count_html" integer DEFAULT NULL,
  "views_xml" integer DEFAULT NULL,
  "views_html" integer DEFAULT NULL,
  "lastvisit_xml" integer DEFAULT NULL,
  "lastvisit_html" integer DEFAULT NULL,
  PRIMARY KEY ("id")
);

CREATE TABLE "#__xmap_items" (
  "uid" character varying(100) NOT NULL,
  "itemid" integer NOT NULL,
  "view" character varying(10) NOT NULL,
  "sitemap_id" integer NOT NULL,
  "properties" varchar(300) DEFAULT NULL,
  PRIMARY KEY ("uid","itemid","view","sitemap_id")
);

CREATE INDEX "#__xmap_items_idx_uid" on "#__xmap_items" ("uid", "itemid");
CREATE INDEX "#__xmap_items_idx_view" on "#__xmap_items" ("view");
