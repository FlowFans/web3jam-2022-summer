/** @type {import('next').NextConfig} */
const nextConfig = {
  reactStrictMode: true,
  swcMinify: true,
  experimental: {
    images: { allowFutureImage: true },
    urlImports: ['https://cdn.skypack.dev/react-icons/ti']
  }
}

module.exports = nextConfig