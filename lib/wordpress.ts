const WORDPRESS_API_URL = process.env.WORDPRESS_API_URL || "http://localhost/usamawp/wp-json/wp/v2";

export interface WPPost {
  id: number;
  date: string;
  slug: string;
  title: { rendered: string };
  excerpt: { rendered: string };
  content: { rendered: string };
}

export interface WPPage {
  id: number;
  slug: string;
  title: { rendered: string };
  content: { rendered: string };
}

async function wpFetch<T>(path: string, params?: Record<string, string>): Promise<T> {
  const url = new URL(`${WORDPRESS_API_URL}${path}`);
  if (params) {
    for (const [key, value] of Object.entries(params)) {
      url.searchParams.set(key, value);
    }
  }

  const res = await fetch(url.toString());
  if (!res.ok) {
    throw new Error(`WordPress API request failed: ${res.status} ${res.statusText} (${url})`);
  }
  return res.json();
}

export function getPosts(params?: Record<string, string>) {
  return wpFetch<WPPost[]>("/posts", params);
}

export async function getPostBySlug(slug: string) {
  const posts = await wpFetch<WPPost[]>("/posts", { slug });
  return posts[0] ?? null;
}

export function getPages(params?: Record<string, string>) {
  return wpFetch<WPPage[]>("/pages", params);
}

export async function getPageBySlug(slug: string) {
  const pages = await wpFetch<WPPage[]>("/pages", { slug });
  return pages[0] ?? null;
}
