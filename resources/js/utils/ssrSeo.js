// The server renders every public page with its final SEO tags: title, description, canonical (slug URL, locale
// prefix), robots (noindex for bot posts and empty places), hreflang and JSON-LD. Google reads the page after this app
// runs, and the views used to overwrite those tags on first load: robots forced to "index", canonical set to the bare
// address, and a second Product schema with a made-up rating (2026-10-08). While the page still shows what the server
// rendered, the views leave the head alone; after an in-app navigation they update it for the visitor.
let navigations = 0

export function markNavigation() {
  navigations++
}

export function isServerRenderedView() {
  return navigations <= 1
}
