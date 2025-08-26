// resolverPage.js

// package pages
const packagePages = import.meta.glob('../Pages/**/*.jsx')

function resolvePackagePages(name) {
  // Try to match the requested name with our package pages
  const key = Object.keys(packagePages).find((path) => path.endsWith(`${name}.jsx`))

  if (!key) {
    console.warn(`Page "${name}" not found in package pages.`)
    return null
  }

  return packagePages[key]()
}

export function resolvePages(originalResolver) {
  return (name) => {
    try {
      // 1. Try original resolver first (user’s app)
      const page = originalResolver(name)
      if (page) return page
    } catch (e) {
      console.warn(`Page "${name}" not found in user-defined pages. Trying package pages...`)
    }

    const pkgPage = resolvePackagePages(name)
    if (pkgPage) return pkgPage

    throw new Error(`Page "${name}" not found in user app or package.`)
  }
}

