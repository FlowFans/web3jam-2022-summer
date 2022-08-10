import * as sharp from 'sharp';
import { s3 } from '../../services/s3';
import * as ipfsClient from 'ipfs-http-client';

const projectId = '*';
const projectSecret = '*';
const auth = 'Basic ' + Buffer.from(projectId + ':' + projectSecret).toString('base64');

const client = ipfsClient.create({
  host: 'ipfs.infura.io',
  port: 5001,
  protocol: 'https',
  headers: {
    authorization: auth,
  },
});

const uploadBase64ToS3 = async (buf, filename, series) => {
  // const buf = Buffer.from(b64.replace(/^data:image\/\w+;base64,/, ''), 'base64');

  return new Promise((resolve, reject) => {
    const key = `customized/${series.toLocaleLowerCase()}/${filename}.png`;
    console.log('upload to s3: ', key);
    const data = {
      Key: key,
      Body: buf,
      Bucket: process.env.NEXT_PUBLIC_AWS_S3_BUCKET_NAME,
      ContentEncoding: 'base64',
      ContentType: 'image/png',
    };
    s3.putObject(data, (err, res) => {
      if (err) {
        console.log('upload failed, err: ', err);
        reject(err);
      } else {
        console.log('upload successfully, res: ', res);
        resolve();
      }
    });
  });
};

const bufferS3Image = async url => {
  const downloadParams = {
    Key: url,
    Bucket: process.env.NEXT_PUBLIC_AWS_S3_BUCKET_NAME,
  };

  const downloadedObject = await s3.getObject(downloadParams).promise();
  return downloadedObject.Body;
};

const uploadBase64ToIPFS = async base64 => {
  const buf = Buffer.from(base64, 'base64');
  const data = await client.add(buf);
  return data.path;
};

export default async function handler(req, res) {
  const { assets } = req.body;

  const assetImages = assets
    .sort((a, b) => a.layer < b.layer)
    .map(asset => {
      return {
        url: `${asset.series.toLocaleLowerCase()}/original/${asset.ipfsHash}.png`,
        layer: asset.layer,
      };
    });

  const buffers = await Promise.all(assetImages.map(x => bufferS3Image(x.url)));

  const buffersInput = buffers.map(x => ({ input: x }));

  try {
    const buf = await sharp(buffers[0]).composite(buffersInput).png().toBuffer();
    const b64 = buf.toString('base64');

    const ipfsHash = await uploadBase64ToIPFS(`data:image/png;base64,${b64}`);
    console.log('new ipfsHash: ', ipfsHash);
    await uploadBase64ToS3(buf, ipfsHash, assets[0].series);
    res.status(200).json({ ipfsHash });
  } catch (e) {
    console.log(e);
  }
}

export const config = {
  api: {
    bodyParser: {
      sizeLimit: '10mb',
    },
    responseLimit: '10mb',
  },
};
