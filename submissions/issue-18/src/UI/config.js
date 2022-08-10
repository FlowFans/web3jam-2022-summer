export const s3Loader = ({ series = '', src, isOriginal }) => {
  if (isOriginal) {
    return `${process.env.NEXT_PUBLIC_AWS_S3_BUCKET}/${series.toLocaleLowerCase()}/original/${src}.png`;
  } else {
    return `${process.env.NEXT_PUBLIC_AWS_S3_BUCKET}/${series.toLocaleLowerCase()}/display/${src}.png`;
  }
};

