FROM node:22-alpine
WORKDIR /app
COPY package*.json ./
RUN npm ci --omit=dev
COPY server.mjs ./
USER node
ENV REMOTE_RELAY_HOST=0.0.0.0 PORT=8081
EXPOSE 8081
CMD ["node","server.mjs"]
