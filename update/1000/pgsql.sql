ALTER TABLE "images" ADD "image_tags" TEXT;
ALTER TABLE "posts" ADD "post_category" TEXT;
ALTER TABLE "pages" ADD "page_category" TEXT;
ALTER TABLE "users" ADD "user_uri" TEXT;
ALTER TABLE "tags" ADD "tag_parents" TEXT;
ALTER TABLE "guest_inclusive" ADD "guest_inclusive" INTEGER;