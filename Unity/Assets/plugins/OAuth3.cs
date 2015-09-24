﻿using System;
using System.Collections.Generic;
using System.Net;
using System.Security.Cryptography;
using System.Text;
//using System.Web;

namespace OAuth3
{
    //
    // Configuration information for an OAuth client
    //
    public class OAuthConfig
    {
        // keys, callbacks
        public string ConsumerKey, Callback, ConsumerSecret, TwitPicKey, BitlyKey;

        // Urls
        public string RequestTokenUrl, AccessTokenUrl, AuthorizeUrl;
    }

    //
    // The authorizer uses a config and an optional xAuth user/password
    // to perform the OAuth authorization process as well as signing
    // outgoing http requests
    //
    // To get an access token, you use these methods in the workflow:
    // 	  AcquireRequestToken
    //    AuthorizeUser
    //
    // These static methods only require the access token:
    //    AuthorizeRequest
    //    AuthorizeTwitPic
    //
    public class OAuthAuthorizer
    {
        // Settable by the user
        public string xAuthUsername, xAuthPassword;

        OAuthConfig config;
        string RequestToken, RequestTokenSecret;
        string AuthorizationToken, AuthorizationVerifier;
        public string AccessToken, AccessTokenSecret, AccessScreenname;
        public long AccessId;

        // Constructor for standard OAuth
        public OAuthAuthorizer(OAuthConfig config)
        {
            this.config = config;
        }

        // Constructor for xAuth
        public OAuthAuthorizer(OAuthConfig config, string xAuthUsername, string xAuthPassword)
        {
            this.config = config;
            this.xAuthUsername = xAuthUsername;
            this.xAuthPassword = xAuthPassword;
        }

        static Random random = new Random();
        static DateTime UnixBaseTime = new DateTime(1970, 1, 1);

        // 16-byte lower-case or digit string
        static string MakeNonce()
        {
			return Guid.NewGuid().ToString();
            
        }

        static string MakeTimestamp()
        {
            return ((long)(DateTime.UtcNow - UnixBaseTime).TotalSeconds).ToString();
        }

        // Makes an OAuth signature out of the HTTP method, the base URI and the headers
        static string MakeSignature(string method, string base_uri, Dictionary<string, string> headers)
        {
            List<string> keys = new List<string>(headers.Keys);
            keys.Sort();
            List<string> items = new List<string>();
            foreach(string key in keys)
            {
                items.Add(key+ "%3D" + OAuth.PercentEncode(headers[key]));
            }

            return method + "&" + OAuth.PercentEncode(base_uri) + "&" +
                string.Join("%26", items.ToArray());
        }

        static string MakeSigningKey(string consumerSecret, string oauthTokenSecret)
        {
            return OAuth.PercentEncode(consumerSecret) + "&" + (oauthTokenSecret != null ? OAuth.PercentEncode(oauthTokenSecret) : "");
        }

        static string MakeOAuthSignature(string compositeSigningKey, string signatureBase)
        {
            var sha1 = new HMACSHA1(Encoding.UTF8.GetBytes(compositeSigningKey));

            return Convert.ToBase64String(sha1.ComputeHash(Encoding.UTF8.GetBytes(signatureBase)));
        }

        static string HeadersToOAuth(Dictionary<string, string> headers)
        {
            List<string> keys = new List<string>(headers.Keys);
            keys.Sort();
            List<string> items = new List<string>();
            foreach(string key in keys) items.Add(string.Format("{0}=\"{1}\"", key, headers[key]));
            return "OAuth " + String.Join(",", items.ToArray());
        }

		static string HeadersToOAuth2(Dictionary<string, string> headers)
		{
			List<string> keys = new List<string>(headers.Keys);
			keys.Sort();
			List<string> items = new List<string>();
			foreach(string key in keys) items.Add(string.Format("{0}={1}", key, headers[key]));
			return String.Join("&", items.ToArray());
		}

        public bool AcquireRequestToken()
        {
            var headers = new Dictionary<string, string>() {
				{ "oauth_callback", OAuth.PercentEncode (config.Callback) },
				{ "oauth_consumer_key", config.ConsumerKey },
				{ "oauth_nonce", MakeNonce () },
				{ "oauth_signature_method", "HMAC-SHA1" },
				{ "oauth_timestamp", MakeTimestamp () },
				{ "oauth_version", "1.0" }};

            string signature = MakeSignature("POST", config.RequestTokenUrl, headers);
            string compositeSigningKey = MakeSigningKey(config.ConsumerSecret, null);
            string oauth_signature = MakeOAuthSignature(compositeSigningKey, signature);

            var wc = new WebClient();
            headers.Add("oauth_signature", OAuth.PercentEncode(oauth_signature));
            wc.Headers[HttpRequestHeader.Authorization] = HeadersToOAuth(headers);

            try
            {
                var result = Nancy.Helpers.HttpUtility.ParseQueryString(wc.UploadString(new Uri(config.RequestTokenUrl), ""));

                if (result["oauth_callback_confirmed"] != null)
                {
                    RequestToken = result["oauth_token"];
                    RequestTokenSecret = result["oauth_token_secret"];

                    return true;
                }
            }
            catch (Exception e)
            {
                Console.WriteLine(e);
                // fallthrough for errors
            }
            return false;
        }

        // Invoked after the user has authorized us
        //
        // TODO: this should return the stream error for invalid passwords instead of
        // just true/false.
        public bool AcquireAccessToken()
        {
            var headers = new Dictionary<string, string>() {
				{ "oauth_consumer_key", config.ConsumerKey },
				{ "oauth_nonce", MakeNonce () },
				{ "oauth_signature_method", "HMAC-SHA1" },
				{ "oauth_timestamp", MakeTimestamp () },
				{ "oauth_version", "1.0" }};
            var content = "";
            if (xAuthUsername == null)
            {
                headers.Add("oauth_token", OAuth.PercentEncode(AuthorizationToken));
                headers.Add("oauth_verifier", OAuth.PercentEncode(AuthorizationVerifier));
            }
            else
            {
                headers.Add("x_auth_username", OAuth.PercentEncode(xAuthUsername));
                headers.Add("x_auth_password", OAuth.PercentEncode(xAuthPassword));
                headers.Add("x_auth_mode", "client_auth");
                content = String.Format("x_auth_mode=client_auth&x_auth_password={0}&x_auth_username={1}", OAuth.PercentEncode(xAuthPassword), OAuth.PercentEncode(xAuthUsername));
            }

            string signature = MakeSignature("POST", config.AccessTokenUrl, headers);
            string compositeSigningKey = MakeSigningKey(config.ConsumerSecret, RequestTokenSecret);
            string oauth_signature = MakeOAuthSignature(compositeSigningKey, signature);

            var wc = new WebClient();
            headers.Add("oauth_signature", OAuth.PercentEncode(oauth_signature));
            if (xAuthUsername != null)
            {
                headers.Remove("x_auth_username");
                headers.Remove("x_auth_password");
                headers.Remove("x_auth_mode");
            }
            wc.Headers[HttpRequestHeader.Authorization] = HeadersToOAuth(headers);

            try
            {
                var result = Nancy.Helpers.HttpUtility.ParseQueryString(wc.UploadString(new Uri(config.AccessTokenUrl), content));

                if (result["oauth_token"] != null)
                {
                    AccessToken = result["oauth_token"];
                    AccessTokenSecret = result["oauth_token_secret"];
                    AccessScreenname = result["screen_name"];
                    AccessId = Int64.Parse(result["user_id"]);

                    return true;
                }
            }
            catch (WebException e)
            {
                var x = e.Response.GetResponseStream();
                var j = new System.IO.StreamReader(x);
                Console.WriteLine(j.ReadToEnd());
                Console.WriteLine(e);
                // fallthrough for errors
            }
            return false;
        }


        // 
        // Assign the result to the Authorization header, like this:
        // request.Headers [HttpRequestHeader.Authorization] = AuthorizeRequest (...)
        //
        public static string AuthorizeRequest(OAuthConfig config, string oauthToken, string oauthTokenSecret, string method, Uri uri, string data)
        {
            var headers = new Dictionary<string, string>() {
				{ "oauth_consumer_key", config.ConsumerKey },
				{ "oauth_nonce", MakeNonce () },
				{ "oauth_signature_method", "HMAC-SHA1" },
				{ "oauth_timestamp", MakeTimestamp () },
				{ "oauth_token", oauthToken },
				{ "oauth_version", "1.0" }};
            var signatureHeaders = new Dictionary<string, string>(headers);

            // Add the data and URL query string to the copy of the headers for computing the signature
            if (data != null && data != "")
            {
                var parsed = Nancy.Helpers.HttpUtility.ParseQueryString(data);
                foreach (string k in parsed.Keys)
                {
                    signatureHeaders.Add(k, OAuth.PercentEncode(parsed[k]));
                }
            }

            var nvc = Nancy.Helpers.HttpUtility.ParseQueryString(uri.Query);
            foreach (string key in nvc)
            {
                if (key != null)
                    signatureHeaders.Add(key, OAuth.PercentEncode(nvc[key]));
            }

            string signature = MakeSignature(method, uri.GetLeftPart(UriPartial.Path), signatureHeaders);
            string compositeSigningKey = MakeSigningKey(config.ConsumerSecret, oauthTokenSecret);
            string oauth_signature = MakeOAuthSignature(compositeSigningKey, signature);

            headers.Add("oauth_signature", OAuth.PercentEncode(oauth_signature));

            return HeadersToOAuth(headers);
        }

		
		// 
		// Assign the result to the Authorization header, like this:
		// request.Headers [HttpRequestHeader.Authorization] = AuthorizeRequest (...)
		//
		public static string AuthorizeRequest2(OAuthConfig config, string oauthToken, string oauthTokenSecret, string method, Uri uri, string data)
		{
			var headers = new Dictionary<string, string>() {
				{ "oauth_consumer_key", config.ConsumerKey },
				{ "oauth_nonce", MakeNonce () },
				{ "oauth_signature_method", "HMAC-SHA1" },
				{ "oauth_timestamp", MakeTimestamp () },
				{ "oauth_token", oauthToken },
				{ "oauth_version", "1.0" }};
			var signatureHeaders = new Dictionary<string, string>(headers);
			
			// Add the data and URL query string to the copy of the headers for computing the signature
			if (data != null && data != "")
			{
				var parsed = Nancy.Helpers.HttpUtility.ParseQueryString(data);
				foreach (string k in parsed.Keys)
				{
					signatureHeaders.Add(k, OAuth.PercentEncode(parsed[k]));
				}
			}
			
			var nvc = Nancy.Helpers.HttpUtility.ParseQueryString(uri.Query);
			foreach (string key in nvc)
			{
				if (key != null)
					signatureHeaders.Add(key, OAuth.PercentEncode(nvc[key]));
			}
			
			string signature = MakeSignature(method, uri.GetLeftPart(UriPartial.Path), signatureHeaders);
			string compositeSigningKey = MakeSigningKey(config.ConsumerSecret, oauthTokenSecret);
			string oauth_signature = MakeOAuthSignature(compositeSigningKey, signature);
			
			headers.Add("oauth_signature", OAuth.PercentEncode(oauth_signature));
			
			return HeadersToOAuth2(headers);
		}

		public static Dictionary<string, string> AuthorizeRequest3(OAuthConfig config, string oauthToken, string oauthTokenSecret, string method, Uri uri, string data)
		{
			var headers = new Dictionary<string, string>() {
				{ "oauth_consumer_key", config.ConsumerKey },
				{ "oauth_nonce", MakeNonce () },
				{ "oauth_signature_method", "HMAC-SHA1" },
				{ "oauth_timestamp", MakeTimestamp () },
				{ "oauth_token", oauthToken },
				{ "oauth_version", "1.0" }};
			var signatureHeaders = new Dictionary<string, string>(headers);
			
			// Add the data and URL query string to the copy of the headers for computing the signature
			if (data != null && data != "")
			{
				var parsed = Nancy.Helpers.HttpUtility.ParseQueryString(data);
				foreach (string k in parsed.Keys)
				{
					signatureHeaders.Add(k, OAuth.PercentEncode(parsed[k]));
				}
			}
			
			var nvc = Nancy.Helpers.HttpUtility.ParseQueryString(uri.Query);
			foreach (string key in nvc)
			{
				if (key != null)
					signatureHeaders.Add(key, OAuth.PercentEncode(nvc[key]));
			}
			
			string signature = MakeSignature(method, uri.GetLeftPart(UriPartial.Path), signatureHeaders);
			string compositeSigningKey = MakeSigningKey(config.ConsumerSecret, oauthTokenSecret);
			string oauth_signature = MakeOAuthSignature(compositeSigningKey, signature);
			
			headers.Add("oauth_signature", oauth_signature);
			
			return headers;
		}


        //
        // Used to authorize an HTTP request going to TwitPic
        //
        public static void AuthorizeTwitPic(OAuthConfig config, HttpWebRequest wc, string oauthToken, string oauthTokenSecret)
        {
            var headers = new Dictionary<string, string>() {
				{ "oauth_consumer_key", config.ConsumerKey },
				{ "oauth_nonce", MakeNonce () },
				{ "oauth_signature_method", "HMAC-SHA1" },
				{ "oauth_timestamp", MakeTimestamp () },
				{ "oauth_token", oauthToken },
				{ "oauth_version", "1.0" },
				//{ "realm", "http://api.twitter.com" }
			};
            string signurl = "http://api.twitter.com/1/account/verify_credentials.xml";
            // The signature is not done against the *actual* url, it is done against the verify_credentials.json one 
            string signature = MakeSignature("GET", signurl, headers);
            string compositeSigningKey = MakeSigningKey(config.ConsumerSecret, oauthTokenSecret);
            string oauth_signature = MakeOAuthSignature(compositeSigningKey, signature);

            headers.Add("oauth_signature", OAuth.PercentEncode(oauth_signature));

            
            wc.Headers.Add("X-Verify-Credentials-Authorization", HeadersToOAuth(headers));
            wc.Headers.Add("X-Auth-Service-Provider", signurl);
        }

       
    }

    public static class OAuth
    {

        // 
        // This url encoder is different than regular Url encoding found in .NET 
        // as it is used to compute the signature based on a url.   Every document
        // on the web omits this little detail leading to wasting everyone's time.
        //
        // This has got to be one of the lamest specs and requirements ever produced
        //
        public static string PercentEncode(string s)
        {
            var sb = new StringBuilder();

            foreach (byte c in Encoding.UTF8.GetBytes(s))
            {
                if ((c >= 'a' && c <= 'z') || (c >= 'A' && c <= 'Z') || (c >= '0' && c <= '9') || c == '-' || c == '_' || c == '.' || c == '~')
                    sb.Append((char)c);
                else
                {
                    sb.AppendFormat("%{0:X2}", c);
                }
            }
            return sb.ToString();
        }
    }
}
