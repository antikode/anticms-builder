export default function AntiCmsBuilderPlugin() {
  return {
    name: 'anti-cms-builder',
    config() {
      return {
        resolve: {
          alias: {
            '@anti-cms-builder': '/vendor/antikode/anti-cms-builder/resources/js',
            '@anti-cms-builder-pages': '/vendor/antikode/anti-cms-builder/resources/js/Pages',
          }
        }
      }
    }
  }
}
