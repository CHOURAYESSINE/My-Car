import {cp,mkdir} from 'node:fs/promises';
await mkdir('vercel-public',{recursive:true});
for(const folder of ['assets','css','javaScript']) await cp(folder,`vercel-public/${folder}`,{recursive:true});
