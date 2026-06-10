export function sidebarMarginClass(open: boolean, collapsed: boolean) {
  if (!open) return 'ml-0'
  // Must match the sidebar's width classes in `Sidebar.tsx`
  // (collapsed: w-16, expanded: w-52) so the gap to the main
  // content equals the gap on the right side.
  return collapsed ? 'ml-16' : 'ml-52'
}
